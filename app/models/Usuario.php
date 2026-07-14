<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $table = 'Usuario';
    protected string $pk    = 'Id_Usuario';

    /**
     * Busca un usuario por email.
     * Trae Nombre desde Persona y rol desde Rol para poblar Session::login().
     * No filtra por Activo ni Eliminado: esa validación la hace el controlador.
     */
    public function buscarPorEmail(string $email): array|false
    {
        $sql = "SELECT
                    u.Id_Usuario,
                    u.Id_Persona,
                    p.Nombre,
                    p.Apellido,
                    u.Email,
                    u.Password_Hash,
                    u.Activo,
                    u.Eliminado,
                    r.Nombre AS rol
                FROM Usuario u
                JOIN Persona    p  ON p.Id_Persona = u.Id_Persona
                JOIN UsuarioRol ur ON ur.Id_Usuario = u.Id_Usuario AND ur.Eliminado = 0
                JOIN Rol        r  ON r.Id_Rol      = ur.Id_Rol
                WHERE u.Email = :email
                LIMIT 1";

        return $this->queryOne($sql, [':email' => $email]);
    }

    /**
     * Busca un usuario por ID.
     * Trae Nombre desde Persona y rol desde Rol.
     */
    public function buscarPorId(int $id): array|false
    {
        $sql = "SELECT
                    u.Id_Usuario,
                    u.Id_Persona,
                    p.Nombre,
                    p.Apellido,
                    u.Email,
                    u.Activo,
                    u.Eliminado,
                    r.Nombre AS rol
                FROM Usuario u
                JOIN Persona    p  ON p.Id_Persona = u.Id_Persona
                JOIN UsuarioRol ur ON ur.Id_Usuario = u.Id_Usuario AND ur.Eliminado = 0
                JOIN Rol        r  ON r.Id_Rol      = ur.Id_Rol
                WHERE u.Id_Usuario = :id
                AND u.Eliminado = 0
                LIMIT 1";

        return $this->queryOne($sql, [':id' => $id]);
    }

    /**
     * Registra un nuevo usuario en una transacción atómica.
     * Crea Persona + Usuario + UsuarioRol en un solo bloque.
     * El hash de contraseña debe llegar ya procesado desde el controlador.
     * Devuelve el Id_Usuario generado.
     *
     * @throws \RuntimeException Si la transacción falla.
     */
    public function registrar(array $datos): int
    {
        $this->db->beginTransaction();

        try {
            // 1. Persona
            $stmtPersona = $this->db->prepare("
                INSERT INTO Persona (Nombre, Apellido)
                VALUES (:nombre, :apellido)
            ");
            $stmtPersona->execute([
                ':nombre'   => $datos['Nombre'],
                ':apellido' => $datos['Apellido'],
            ]);
            $idPersona = (int) $this->db->lastInsertId();

            // 2. Usuario
            $stmtUsuario = $this->db->prepare("
                INSERT INTO Usuario (Id_Persona, Email, Password_Hash, Activo, Eliminado)
                VALUES (:id_persona, :email, :password_hash, 1, 0)
            ");
            $stmtUsuario->execute([
                ':id_persona'    => $idPersona,
                ':email'         => $datos['Email'],
                ':password_hash' => $datos['Password_Hash'],
            ]);
            $idUsuario = (int) $this->db->lastInsertId();

            // 3. Rol USUARIO (se busca por nombre, no se hardcodea el ID)
            $stmtRol = $this->db->prepare("
                SELECT Id_Rol FROM Rol WHERE Nombre = 'USUARIO' LIMIT 1
            ");
            $stmtRol->execute();
            $idRol = (int) $stmtRol->fetchColumn();

            if (!$idRol) {
                throw new \RuntimeException("El rol 'USUARIO' no existe en la tabla Rol.");
            }

            $stmtUsuarioRol = $this->db->prepare("
                INSERT INTO UsuarioRol (Id_Usuario, Id_Rol, Eliminado)
                VALUES (:id_usuario, :id_rol, 0)
            ");
            $stmtUsuarioRol->execute([
                ':id_usuario' => $idUsuario,
                ':id_rol'     => $idRol,
            ]);

            $this->db->commit();

            return $idUsuario;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new \RuntimeException('Error al registrar usuario: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Desactiva un usuario (Activo = 0). No elimina.
     */
    public function desactivar(int $id): bool
    {
        return $this->update($id, ['Activo' => 0]);
    }

    /**
     * Soft delete.
     */
    public function eliminar(int $id): bool
    {
        return $this->softDelete($id);
    }
}