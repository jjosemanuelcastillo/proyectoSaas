import { Component, OnInit } from '@angular/core';
import { AuthService } from '../core/service/auth.service';
import { Router } from '@angular/router';

@Component({
  selector: 'app-inicio',
  imports: [],
  templateUrl: './inicio.component.html',
  styleUrl: './inicio.component.css',
})
export class InicioComponent implements OnInit {
  user: any = null;
  constructor(
    private authServcie: AuthService,
    private router: Router,
  ) {}

  ngOnInit(): void {
    this.authServcie.getUser().subscribe({
      next: (res) => {
        this.user = res;

      },

      error: () =>{
        localStorage.removeItem('token');
        this.router.navigate(['/login']);
      }
    });
  }

  get iniciales(): string {
  if (!this.user?.name) return '';
  return this.user.name
    .trim()
    .split(/\s+/)      // separa por espacios: ["Marta", "Gómez"]
    .slice(0, 2)       // se queda con las dos primeras palabras
    .map((p: string) => p[0])  // primera letra de cada una
    .join('')          // "MG"
    .toUpperCase();
}
}
