import jwt from 'jsonwebtoken';
import dotenv from 'dotenv';

dotenv.config();

const SECRET = process.env.JWT_SECRET || 'secretodefault';

export class JwtHelper {
    
    // Genera un token que dura 1 hora
    static generateToken(payload: any): string {
        return jwt.sign(payload, SECRET, { expiresIn: '1h' });
    }

    // Verifica si un token es válido (lo usaremos después si necesitamos proteger rutas en Node)
    static verifyToken(token: string): any {
        try {
            return jwt.verify(token, SECRET);
        } catch (error) {
            return null;
        }
    }
}