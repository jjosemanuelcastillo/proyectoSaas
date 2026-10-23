import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { AuthService } from '../../../core/service/auth.service';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-login',
  imports: [ReactiveFormsModule],
  standalone: true,
  templateUrl: './login.component.html',
  styleUrl: './login.component.css',
})
export class LoginComponent {
  private fb = inject(FormBuilder);
  private authService = inject(AuthService);
  error = signal<string | null>(null);

  form = this.fb.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    password: ['', Validators.required],
  });

  onSubmit() {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.error.set(null);
    this.authService.login(this.form.getRawValue()).subscribe({
      next: (respuesta) => {
        console.log('Logeado', respuesta.user);
      },
      error: (err: HttpErrorResponse) => {
        if (err.status === 401) {
          this.error.set(err.error.message); // "Credenciales incorrectas" desde Laravel
        } else if (err.status === 429) {
          this.error.set('Demasiados intentos. Espera un minuto.');
        } else {
          // status 0 = no hay respuesta (Laravel apagado, sin conexión...)
          this.error.set('No se ha podido conectar con el servidor.');
        }
      },
    });
  }
}
