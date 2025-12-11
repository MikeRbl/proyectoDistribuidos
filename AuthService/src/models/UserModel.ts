import pool from '../config/db';
import { User } from './User';
import { v4 as uuidv4 } from 'uuid';
import { RowDataPacket } from 'mysql2';

class UserModel {

    // Método para crear un nuevo usuario
    static async create(user: User): Promise<User> {
        // Generamos el UUID aquí (versión 4 es la estándar)
        const id = uuidv4();
        
        const query = `
            INSERT INTO usuarios (id, nombre, apellidos, email, password, telefono) 
            VALUES (?, ?, ?, ?, ?, ?)
        `;
        
        // Ejecutamos la consulta
        await pool.execute(query, [
            id,
            user.nombre,
            user.apellidos,
            user.email,
            user.password, // NOTA: Aquí ya debe llegar hasheada (lo haremos en el controlador)
            user.telefono || null
        ]);

        // Retornamos el objeto usuario con el ID generado
        return { ...user, id };
    }

    // Método para buscar usuario por Email (para el Login)
    static async findByEmail(email: string): Promise<User | null> {
        const query = 'SELECT * FROM usuarios WHERE email = ?';
        
        // El tipo <RowDataPacket[]> le dice a TS que esto devuelve filas de BD
        const [rows] = await pool.execute<RowDataPacket[]>(query, [email]);

        if (rows.length > 0) {
            return rows[0] as User;
        }
        
        return null;
    }

    // Método para actualizar la última sesión
    static async updateLastSession(userId: string): Promise<void> {
        const query = 'UPDATE usuarios SET fecha_ultima_sesion = NOW() WHERE id = ?';
        await pool.execute(query, [userId]);
    }
}

export default UserModel;