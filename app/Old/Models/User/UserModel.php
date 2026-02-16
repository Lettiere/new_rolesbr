<?php

namespace App\Models\User;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'type_user',
        'is_active',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $deletedField = 'deleted_at';

    protected $returnType = 'array';

    // Validação desabilitada no model - validação feita no controller
    // Isso evita conflitos e permite mais controle
    protected $validationRules = [];

    protected $validationMessages = [];
}
