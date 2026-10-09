/** Los cuatro roles que puede tener un usuario en la API. */
export type Rol = 'superadmin' | 'dueno' | 'empleado' | 'cliente';

/** Usuario tal como lo devuelve la API (sin contraseña). */
export interface User {
  id: number;
  name: string;
  email: string;
  telefono: string | null;
  rol: Rol;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

/**
 * Respuesta de POST /api/login y POST /api/registro.
 * El login añade además `status` y `message`; el registro no los envía.
 */
export interface AuthResponse {
  user: User;
  token: string;
  status?: boolean;
  message?: string;
}

/** Datos que se envían a POST /api/login. */
export interface LoginRequest {
  email: string;
  password: string;
}

/** Datos que se envían a POST /api/registro. */
export interface RegistroRequest {
  name: string;
  email: string;
  telefono: string;
  password: string;
  password_confirmation: string;
}
