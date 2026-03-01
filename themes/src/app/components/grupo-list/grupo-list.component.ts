import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { GrupoService } from '../../services/grupo.service';
import { Grupo } from '../../models/grupo.model';
import { LucideAngularModule, Plus, Edit2, Trash2, FolderOpen, FileText } from 'lucide-angular';

@Component({
  selector: 'app-grupo-list',
  standalone: true,
  imports: [CommonModule, RouterModule, LucideAngularModule],
  templateUrl: './grupo-list.component.html',
  styleUrls: ['./grupo-list.component.css']
})
export class GrupoListComponent implements OnInit {
  grupos: Grupo[] = [];
  loading = false;
  error: string | null = null;

  readonly PlusIcon = Plus;
  readonly Edit2Icon = Edit2;
  readonly Trash2Icon = Trash2;
  readonly FolderOpenIcon = FolderOpen;
  readonly FileTextIcon = FileText;

  constructor(private grupoService: GrupoService) { }

  ngOnInit(): void {
    this.carregarGrupos();
  }

  carregarGrupos(): void {
    this.loading = true;
    this.error = null;
    
    this.grupoService.listarGrupos().subscribe({
      next: (grupos) => {
        this.grupos = grupos;
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erro ao carregar grupos. Tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  excluirGrupo(id: number, nome: string): void {
    if (!confirm(`Tem certeza que deseja excluir o grupo "${nome}"?`)) {
      return;
    }

    this.grupoService.excluirGrupo(id).subscribe({
      next: () => {
        this.carregarGrupos();
      },
      error: (err) => {
        this.error = 'Erro ao excluir grupo. Tente novamente.';
        console.error(err);
      }
    });
  }
}
