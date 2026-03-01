import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { GrupoService } from '../../services/grupo.service';
import { Grupo } from '../../models/grupo.model';
import { LucideAngularModule, ArrowLeft, Edit2, Trash2, FolderOpen, FileText, Tag } from 'lucide-angular';

@Component({
  selector: 'app-grupo-detail',
  standalone: true,
  imports: [CommonModule, RouterModule, LucideAngularModule],
  templateUrl: './grupo-detail.component.html',
  styleUrls: ['./grupo-detail.component.css']
})
export class GrupoDetailComponent implements OnInit {
  grupo: Grupo | null = null;
  loading = false;
  error: string | null = null;

  readonly ArrowLeftIcon = ArrowLeft;
  readonly Edit2Icon = Edit2;
  readonly Trash2Icon = Trash2;
  readonly FolderOpenIcon = FolderOpen;
  readonly FileTextIcon = FileText;
  readonly TagIcon = Tag;

  constructor(
    private grupoService: GrupoService,
    private route: ActivatedRoute,
    private router: Router
  ) { }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const id = +params['id'];
      if (id) {
        this.carregarGrupo(id);
      }
    });
  }

  carregarGrupo(id: number): void {
    this.loading = true;
    this.error = null;

    this.grupoService.buscarGrupoPorId(id, true).subscribe({
      next: (grupo) => {
        this.grupo = grupo;
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erro ao carregar grupo. Tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  excluirGrupo(): void {
    if (!this.grupo || !confirm(`Tem certeza que deseja excluir o grupo "${this.grupo.nome}"?`)) {
      return;
    }

    this.grupoService.excluirGrupo(this.grupo.id).subscribe({
      next: () => {
        this.router.navigate(['/'], { queryParams: { tab: 'grupos' } });
      },
      error: (err) => {
        this.error = 'Erro ao excluir grupo. Tente novamente.';
        console.error(err);
      }
    });
  }

  voltar(): void {
    this.router.navigate(['/'], { queryParams: { tab: 'grupos' } });
  }
}
