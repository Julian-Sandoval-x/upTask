<?php

namespace Model;

class Usuario extends ActiveRecord
{
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = [])
    {

        // Creamos un "espejo" de los datos que tenemos en la base de datos
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? null;
        $this->password_actual = $args['password_actual'] ?? null;
        $this->password_nuevo = $args['password_nuevo'] ?? null;
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // Validar el login de usuarios
    public function validarLogin(): array
    {
        // Validamos el email
        if (!$this->email) {
            self::$alertas['error'][] = "El email es Obligatorio";
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = "Email no válido";
        }

        // Validamos el password
        if (!$this->password) {
            self::$alertas['error'][] = "El password es Obligatorio";
        }

        return self::$alertas;
    }

    // Validación para cuentas nuevas
    public function validarNuevaCuenta(): array
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = "El nombre del usuario es Obligatorio";
        }

        if (!$this->email) {
            self::$alertas['error'][] = "El email del usuario es Obligatorio";
        }

        // Validamos el password
        if (!$this->password) {
            self::$alertas['error'][] = "El password es Obligatorio";
        }
        // Validamos que el password contenga 6 caracteres
        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = "El password debe contener al menos 6 caracteres";
        }
        if ($this->password !== $this->password2) {
            self::$alertas['error'][] = "Los passwords no coinciden";
        }


        return self::$alertas;
    }

    // Comprobamos que el password actual sea correcto
    public function comprobar_password(): bool
    {
        return password_verify($this->password_actual, $this->password);
    }

    // Hashea el password
    public function hashPassword(): void
    {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Generar un token
    public function crearToken(): void
    {
        $this->token = uniqid();
    }

    // Valida un email
    public function validarEmail(): array
    {
        if (!$this->email) {
            self::$alertas['error'][] = "El email es Obligatorio";
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = "Email no válido";
        }

        return self::$alertas;
    }

    public function validarPassword(): array
    {
        if (!$this->password) {
            self::$alertas['error'][] = "El password es Obligatorio";
        }

        if (strlen($this->password) < 6) {
            self::$alertas['error'][] = "El password debe tener al menos 6 caracteres";
        }

        return self::$alertas;
    }

    public function nuevo_password(): array
    {
        if (!$this->password_actual) {
            self::$alertas['error'][] = "El password Actual es Obligatorio";
        }

        if (!$this->password_nuevo) {
            self::$alertas['error'][] = "El password Nuevo es Obligatorio";
        }

        if (strlen($this->password_nuevo) < 6) {
            self::$alertas['error'][] = "El password debe contener al menos 6 caracteres";
        }

        return self::$alertas;
    }

    public function validar_perfil(): array
    {
        if (!$this->nombre) {
            self::$alertas['error'][] = "El Nombre es Obligatorio";
        }

        if (!$this->email) {
            self::$alertas['error'][] = "El Email es Obligatorio";
        }

        return self::$alertas;
    }
}
