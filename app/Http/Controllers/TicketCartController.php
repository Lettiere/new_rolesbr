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
            'convidados' => 'nullable|array',
            'convidados.*.nome' => 'nullable|string',
            'convidados.*.cpf' => 'nullable|string',
            'convidados.*.data_nascimento' => 'nullable|date',
            'convidados.*.email' => 'nullable|email',
        ]);

        $cpfDigits = preg_replace('/\D+/', '', (string) ($data['cpf'] ?? ''));
        if ($cpfDigits === '') {
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

        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        for ($i = 0; $i < $quantidade; $i++) {
            DB::table('evt_ingressos_vendidos_tb')->insert([
                'evento_id' => $event->evento_id,
                'lote_id' => $ticket->lote_id,
                'user_id' => $user ? $user->id : null,
                'nome_comprador' => $data['nome'] ?? ($user ? $user->name : ''),
                'email_comprador' => $data['email'] ?? ($user ? $user->email : ''),
                'codigo_unico' => $codigo,
                'status' => 'pago',
                'valor_pago' => $ticket->preco,
                'data_compra' => now(),
            ]);
        }

        $titularNome = $data['nome'] ?? ($user ? $user->name : '');
        $titularEmail = $data['email'] ?? ($user ? $user->email : '');

        // Registra o titular como um "convidado" is_titular = true
        DB::table('evt_ingressos_convidados_tb')->insert([
            'evento_id' => $event->evento_id,
            'lote_id' => $ticket->lote_id,
            'codigo_unico' => $codigo,
            'is_titular' => true,
            'titular_nome' => $titularNome,
            'titular_cpf' => $cpfDigits,
            'nome' => $titularNome,
            'cpf' => $cpfDigits,
            'data_nascimento' => null,
            'email' => $titularEmail,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $extrasEsperados = max(0, $quantidade - 1);
        $convidados = $request->input('convidados', []);
        if ($extrasEsperados > 0) {
            // Garante que temos exatamente a quantidade de convidados esperados
            $convidados = array_slice($convidados, 0, $extrasEsperados);
            foreach ($convidados as $cv) {
                $nomeCv = trim((string) ($cv['nome'] ?? ''));
                if ($nomeCv === '') {
                    continue;
                }
                $cpfCv = preg_replace('/\D+/', '', (string) ($cv['cpf'] ?? ''));
                $nasc = !empty($cv['data_nascimento'] ?? null) ? $cv['data_nascimento'] : null;
                $emailCv = $cv['email'] ?? null;

                DB::table('evt_ingressos_convidados_tb')->insert([
                    'evento_id' => $event->evento_id,
                    'lote_id' => $ticket->lote_id,
                    'codigo_unico' => $codigo,
                    'is_titular' => false,
                    'titular_nome' => $titularNome,
                    'titular_cpf' => $cpfDigits,
                    'nome' => $nomeCv,
                    'cpf' => $cpfCv ?: null,
                    'data_nascimento' => $nasc,
                    'email' => $emailCv,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return redirect()->route('site.ticket.show', $ticket->lote_id)
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
