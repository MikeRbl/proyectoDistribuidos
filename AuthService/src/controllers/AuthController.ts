import { Request, Response } from 'express';
import bcrypt from 'bcrypt';
import UserModel from '../models/UserModel';
import axios from 'axios';
import { JwtHelper } from '../utils/jwt';

export class AuthController {

    static async register(req: Request, res: Response): Promise<void> {
        try {
            const { nombre, apellidos, email, password, telefono } = req.body;

            // 1. Validar si el usuario ya existe
            const existingUser = await UserModel.findByEmail(email);
            if (existingUser) {
                res.status(400).json({ message: 'El usuario ya existe con ese email.' });
                return;
            }

            // 2. Hashear la contraseña (Requisito del PDF)
            const saltRounds = 10;
            const hashedPassword = await bcrypt.hash(password, saltRounds);

            // 3. Guardar en MySQL
            const newUser = await UserModel.create({
                nombre,
                apellidos,
                email,
                password: hashedPassword,
                telefono
            });

            // 4. Comunicar con microservicio .NET para enviar correo (Pipedream)
            // NOTA: Asumimos que el servicio .NET correrá en el puerto 5000 (lo haremos en el siguiente gran paso)
            try {
                await axios.post('http://localhost:5000/api/Pipedream/welcome', {
                    email: newUser.email,
                    nombre: newUser.nombre
                });
                console.log('Solicitud de correo enviada a .NET');
            } catch (error) {
                console.error('No se pudo contactar al servicio de correos .NET. ¿Está encendido?', error);
                // No bloqueamos el registro si falla el correo, pero lo logueamos
            }

            // Responder al frontend
            res.status(201).json({
                message: 'Usuario registrado exitosamente',
                user: {
                    id: newUser.id,
                    email: newUser.email,
                    nombre: newUser.nombre
                }
            });

            

        } catch (error) {
            console.error('Error en el registro:', error);
            res.status(500).json({ message: 'Error interno del servidor' });
        }
    }

    static async login(req: Request, res: Response): Promise<void> {
        try {
            const { email, password } = req.body;
            
            // 1. Buscar al usuario
            const user = await UserModel.findByEmail(email);
            
            if (!user) {
                res.status(401).json({ message: 'Credenciales inválidas' });
                return;
            }

            // 2. Verificar contraseña
            const isPasswordValid = await bcrypt.compare(password, user.password || '');
            
            if (!isPasswordValid) {
                res.status(401).json({ message: 'Credenciales inválidas' });
                return;
            }

            // 3. Actualizar fecha última sesión
            if (user.id) {
                await UserModel.updateLastSession(user.id);
            }

            // 4. Generar Token
            const token = JwtHelper.generateToken({
                id: user.id,
                email: user.email,
                nombre: user.nombre
            });

            // 5. Responder
            res.json({
                message: 'Login exitoso',
                token: token,
                user: {
                    id: user.id,
                    nombre: user.nombre,
                    email: user.email
                }
            });

        } catch (error) {
            console.error('Error en login:', error);
            res.status(500).json({ message: 'Error interno del servidor' });
        }
    }

    static async forgotPassword(req: Request, res: Response): Promise<void> {
        try {
            const { email } = req.body;

            // 1. Validar que el usuario existe
            const user = await UserModel.findByEmail(email);
            if (!user) {
                // Por seguridad, no decimos si el correo existe o no, pero retornamos éxito
                res.json({ message: 'Si el correo existe, se enviará un código.' });
                return;
            }

            // 2. Solicitar envío de código al servicio .NET
            // NOTA: En un caso real, guardarías este código en la BD (tabla de recuperación) 
            // para validarlo después. Como el PDF dice que Pipedream genera el código, 
            // asumimos un flujo simplificado o que Pipedream te lo devuelve (lo cual es complejo asíncronamente).
            // Para cumplir el requisito académico: Enviamos solicitud a .NET.
            
            try {
                await axios.post('http://localhost:5000/api/Pipedream/verification-code', {
                    email: user.email,
                    nombre: user.nombre // Enviamos nombre para la plantilla
                });
            } catch (error) {
                console.error('Error contactando mail-service:', error);
                res.status(500).json({ message: 'Error al enviar el correo' });
                return;
            }

            res.json({ message: 'Código de verificación enviado a su correo' });

        } catch (error) {
            console.error(error);
            res.status(500).json({ message: 'Error interno' });
        }
    }

    static async verifyCode(req: Request, res: Response): Promise<void> {
        try {
            const { email, code } = req.body;

            // Llamada al microservicio .NET
            try {
                await axios.post('http://localhost:5000/api/Pipedream/verify-code', {
                    email: email,
                    code: code
                });
                
                // Si llegamos aquí, es un 200 OK
                res.json({ message: 'Verificación exitosa', verified: true });
                
            } catch (error: any) {
                // Si .NET devuelve 400 (Bad Request), axios lanza error
                if (error.response && error.response.status === 400) {
                     res.status(400).json({ message: 'El código es incorrecto', verified: false });
                } else {
                     console.error('Error al verificar:', error);
                     res.status(500).json({ message: 'Error del servidor', verified: false });
                }
            }
        } catch (error) {
            res.status(500).json({ message: 'Error interno' });
        }
    }

}