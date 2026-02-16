<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventTicketLot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketCartController extends Controller
{
    protected function isValidCpf(string $cpf): bool
    {
        $cpf = preg_replace('/\D+/', '', $cpf);
        if (strlen($cpf) != 11) return false;
        if (preg_match('/^(\d)\1{10}$/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int)$cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ((int)$cpf[$c] !== $d) return false;
        }
        return true;
    }
    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $cart = $request->session()->get('ticket_cart', []);
        $items = [];
        $total = 0.0;
        foreach ($cart as $row) {
            $lot = EventTicketLot::find($row['lote_id']);
            if (!$lot) continue;
            $qtd = (int)$row['qty'];
            $preco = (float) ($lot->preco ?? 0);
            $subtotal = $preco * $qtd;
            $items[] = ['lot'=>$lot,'qty'=>$qtd,'subtotal'=>$subtotal];
            $total += $subtotal;
        }
        return view('tickets.cart.index', compact('items','total'));
    }

    public function add(Request $request)
    {
        if (!Auth::check()) return response()->json(['error'=>'unauthorized'], 401);
        $data = $request->validate([
            'lote_id' => 'required|integer',
            'qty' => 'required|integer|min:1|max:10',
        ]);
        $lot = EventTicketLot::findOrFail($data['lote_id']);
        $cart = $request->session()->get('ticket_cart', []);
        $found = false;
        foreach ($cart as &$row) {
            if ($row['lote_id'] == $lot->lote_id) {
                $row['qty'] = min(10, $row['qty'] + $data['qty']);
                $found = true;
                break;
            }
        }
        if (!$found) $cart[] = ['lote_id'=>$lot->lote_id,'qty'=>$data['qty']];
        $request->session()->put('ticket_cart', $cart);
        return response()->json(['ok'=>true]);
    }

    public function remove(Request $request)
    {
        if (!Auth::check()) return back();
        $loteId = (int)$request->input('lote_id');
        $cart = $request->session()->get('ticket_cart', []);
        $cart = array_values(array_filter($cart, fn($r)=>$r['lote_id'] != $loteId));
        $request->session()->put('ticket_cart', $cart);
        return back();
    }

    public function checkout(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $cart = $request->session()->get('ticket_cart', []);
        if (empty($cart)) return redirect()->route('tickets.cart.index');
        return view('tickets.cart.checkout');
    }

    public function publicCheckoutForm(Request $request, $loteId)
    {
        $ticket = EventTicketLot::findOrFail((int) $loteId);
        $event = Event::with('establishment')->find($ticket->evento_id);
        return view('site.ticket_checkout', [
            'ticket' => $ticket,
            'event' => $event,
        ]);
    }

    public function publicCheckoutSubmit(Request $request, $loteId)
    {
        $data = $request->validate([
            'cpf' => 'required|string',
            'quantidade' => 'nullable|integer|min:1|max:5',
            'aceite' => 'accepted',
            'nome' => 'nullable|string',
            'email' => 'nullable|email',
            'telefone' => 'nullable|string',
            'whatsapp_titular' => 'required|string',
            'convidados' => 'nullable|array',
            'convidados.*.nome' => 'nullable|string',
            'convidados.*.cpf' => 'nullable|string',
            'convidados.*.data_nascimento' => 'nullable|date',
            'convidados.*.email' => 'nullable|email',
        ]);

        $cpfDigits = preg_replace('/\D+/', '', (string) ($data['cpf'] ?? ''));
        if ($cpfDigits === '' || !$this->isValidCpf($cpfDigits)) {
            return back()->withErrors(['cpf' => 'CPF inválido'])->withInput();
        }

        $quantidade = (int) ($data['quantidade'] ?? 1);
        if ($quantidade < 1) $quantidade = 1;
        if ($quantidade > 5) $quantidade = 5;

        $ticket = EventTicketLot::findOrFail((int) $loteId);
        $event = Event::findOrFail($ticket->evento_id);

        $perfil = DB::table('perfil_usuarios_tb')->where('cpf', $cpfDigits)->first();

        if (!$perfil) {
            if (empty($data['nome']) || empty($data['email'])) {
                return back()
                    ->with('cpf_requires_register', true)
                    ->withErrors(['cpf' => 'CPF não cadastrado. Preencha nome e e-mail para criar seu cadastro.'])
                    ->withInput();
            }

            $user = User::where('email', $data['email'])->first();
            if (!$user) {
                $user = User::create([
                    'name' => $data['nome'],
                    'email' => $data['email'],
                    'password' => bin2hex(random_bytes(6)),
                    'role' => 'client',
                    'type_user' => 2,
                    'is_active' => true,
                ]);
            }

            DB::table('perfil_usuarios_tb')->insert([
                'user_id' => $user->id,
                'cpf' => $cpfDigits,
                'telefone' => $data['telefone'] ?? '',
            ]);

            $perfil = DB::table('perfil_usuarios_tb')->where('cpf', $cpfDigits)->first();
        } else {
            $user = User::find($perfil->user_id);
        }

        if ($user) {
            Auth::login($user);
        }

        $titularNome  = $data['nome'] ?? ($user ? $user->name  : '');
        $titularEmail = $data['email'] ?? ($user ? $user->email : '');
        $titularWhats = $data['whatsapp_titular'] ?? '';

        $extrasEsperados = max(0, $quantidade - 1);
        $convidados = $request->input('convidados', []);
        $convidados = array_slice(is_array($convidados) ? $convidados : [], 0, $extrasEsperados);

        // Monta lista sequencial de participantes: [titular, convidado1, convidado2, ...]
        $participantes = [];
        $participantes[] = [
            'is_titular' => true,
            'nome' => $titularNome,
            'cpf' => $cpfDigits,
            'data_nascimento' => null,
            'email' => $titularEmail,
        ];
        foreach ($convidados as $cv) {
            $participantes[] = [
                'is_titular' => false,
                'nome' => trim((string) ($cv['nome'] ?? '')),
                'cpf' => preg_replace('/\D+/', '', (string) ($cv['cpf'] ?? '')) ?: null,
                'data_nascimento' => !empty($cv['data_nascimento'] ?? null) ? $cv['data_nascimento'] : null,
                'email' => $cv['email'] ?? null,
            ];
        }

        // Garante que teremos exatamente $quantidade participantes (completa com placeholders se necessário)
        while (count($participantes) < $quantidade) {
            $participantes[] = [
                'is_titular' => false,
                'nome' => $titularNome,
                'cpf' => null,
                'data_nascimento' => null,
                'email' => null,
            ];
        }
        // Corta excedentes se vieram mais convidados do que o permitido
        $participantes = array_slice($participantes, 0, $quantidade);

        $voucherCodes = [];
        foreach ($participantes as $p) {
            // Gera um código único por ingresso e garante unicidade
            do {
                $codigo = strtoupper(substr(bin2hex(random_bytes(8)), 0, 8));
                $exists = DB::table('evt_ingressos_vendidos_tb')->where('codigo_unico', $codigo)->exists();
            } while ($exists);

            DB::table('evt_ingressos_vendidos_tb')->insert([
                'evento_id' => $event->evento_id,
                'lote_id' => $ticket->lote_id,
                'user_id' => $user ? $user->id : null,
                'nome_comprador' => $titularNome,
                'email_comprador' => $titularEmail,
                'codigo_unico' => $codigo,
                'status' => 'pago',
                'valor_pago' => $ticket->preco,
                'data_compra' => now(),
            ]);

            DB::table('evt_ingressos_convidados_tb')->insert([
                'evento_id' => $event->evento_id,
                'lote_id' => $ticket->lote_id,
                'codigo_unico' => $codigo,
                'is_titular' => (bool) $p['is_titular'],
                'titular_nome' => $titularNome,
                'titular_cpf' => $cpfDigits,
                'nome' => $p['nome'] ?: $titularNome,
                'cpf' => $p['cpf'],
                'data_nascimento' => $p['data_nascimento'],
                'email' => $p['email'] ?: $titularEmail,
                'whatsapp' => $titularWhats,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $voucherCodes[] = [
                'codigo' => $codigo,
                'nome' => $p['nome'] ?: $titularNome,
                'email' => $p['email'] ?: $titularEmail,
                'is_titular' => (bool) $p['is_titular'],
            ];
        }

        DB::table('evt_lotes_ingressos_tb')
            ->where('lote_id', $ticket->lote_id)
            ->increment('quantidade_vendida', count($participantes));

        return redirect()->route('site.ticket.show', $ticket->lote_id)
            ->with('voucher', [
                'evento_id' => $event->evento_id,
                'lote_id' => $ticket->lote_id,
                'evento_nome' => $event->nome,
                'lote_nome' => $ticket->nome,
                'valor' => $ticket->preco,
                'titular_nome' => $titularNome,
                'titular_whatsapp' => $titularWhats,
                'codes' => $voucherCodes,
            ])
            ->with('success', 'Compra registrada com sucesso!');
    }

    public function publicRegisterInline(Request $request)
    {
        $validated = $request->validate([
            'cpf' => 'required|string',
            'nome' => 'required|string',
            'email' => 'required|email',
            'telefone' => 'nullable|string',
        ]);

        $cpfDigits = preg_replace('/\D+/', '', (string) $validated['cpf']);
        if ($cpfDigits === '') {
            return response()->json(['errors' => ['cpf' => ['CPF inválido']]], 422);
        }

        $perfil = DB::table('perfil_usuarios_tb')->where('cpf', $cpfDigits)->first();
        if ($perfil) {
            $user = User::find($perfil->user_id);
            if ($user) {
                Auth::login($user);
            }
            return response()->json(['ok' => true]);
        }

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            $user = User::create([
                'name' => $validated['nome'],
                'email' => $validated['email'],
                'password' => bin2hex(random_bytes(6)),
                'role' => 'client',
                'type_user' => 2,
                'is_active' => true,
            ]);
        }

        DB::table('perfil_usuarios_tb')->insert([
            'user_id' => $user->id,
            'cpf' => $cpfDigits,
            'telefone' => $validated['telefone'] ?? '',
        ]);

        Auth::login($user);

        return response()->json(['ok' => true]);
    }
}
