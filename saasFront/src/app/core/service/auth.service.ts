import { HttpClient } from '@angular/common/http';
import { computed, inject, Injectable, signal } from '@angular/core';
import { environment } from '../../../environments/environment';
import { AuthResponse, RegistroRequest, User } from '../models/auth.models';
import { Observable, tap } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private apiUrl = environment.apiUrl;
  private http = inject(HttpClient);
  usuario = signal<User | null>(null);
  estaLogueado = computed(() => this.usuario() !== null);
  esDueno = computed(() => this.usuario()?.rol === 'dueno');

  private guardarSesion(respuesta: AuthResponse): void {
    localStorage.setItem('token', respuesta.token);
    this.usuario.set(respuesta.user);
  }
  registro(datos: RegistroRequest): Observable<AuthResponse> {
    return this.http.post<AuthResponse>(`${this.apiUrl}/registro`, datos).pipe(
      tap((respuesta) => this.guardarSesion(respuesta))
    );
  }
}
