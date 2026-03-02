import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, ActivatedRoute } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { RecursoService } from '../../services/recurso.service';
import { Recurso } from '../../models/recurso.model';
import { LucideAngularModule, Search, Filter, X, Plus, Edit2, Trash2, ChevronDown, Video, FileText, Link2, Tag } from 'lucide-angular';
import { GrupoListComponent } from '../grupo-list/grupo-list.component';

@Component({
  selector: 'app-recurso-list',
  standalone: true,
  imports: [CommonModule, RouterModule, FormsModule, LucideAngularModule, GrupoListComponent],
  templateUrl: './recurso-list.component.html',
  styleUrls: ['./recurso-list.component.css']
})
export class RecursoListComponent implements OnInit {
  recursos: Recurso[] = [];
  loading = false;
  error: string | null = null;
  currentPage = 1;
  totalPages = 1;
  total = 0;
  videoCount = 0;
  pdfCount = 0;
  linkCount = 0;
  countsLoaded = false;
  expandedRecursos: Set<number> = new Set();
  activeTab: 'lista' | 'grupos' = 'lista';
  searchTerm: string = '';
  filterTipo: string = '';
  filterTags: string[] = [];
  availableTags: string[] = [];
  showFilters: boolean = false;

  readonly SearchIcon = Search;
  readonly FilterIcon = Filter;
  readonly XIcon = X;
  readonly PlusIcon = Plus;
  readonly Edit2Icon = Edit2;
  readonly Trash2Icon = Trash2;
  readonly ChevronDownIcon = ChevronDown;
  readonly VideoIcon = Video;
  readonly FileTextIcon = FileText;
  readonly Link2Icon = Link2;
  readonly TagIcon = Tag;

  constructor(
    private recursoService: RecursoService,
    private route: ActivatedRoute
  ) { }

  ngOnInit(): void {
    this.carregarRecursos();
    this.carregarTagsDisponiveis();
  }

  carregarRecursos(): void {
    this.loading = true;
    this.error = null;
    
    this.recursoService.listarRecursos(
      this.currentPage, 
      10, 
      this.searchTerm,
      this.filterTipo || undefined,
      this.filterTags.length > 0 ? this.filterTags : undefined
    ).subscribe({
      next: (response) => {
        this.recursos = response.data;
        this.currentPage = response.current_page;
        this.totalPages = response.last_page;
        this.total = response.total;
        this.calcularContagemPorTipo();
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erro ao carregar recursos. Tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  carregarTagsDisponiveis(): void {
    this.recursoService.listarRecursos(1, 1000).subscribe({
      next: (response) => {
        const tagsSet = new Set<string>();
        response.data.forEach(recurso => {
          recurso.tags.forEach(tag => tagsSet.add(tag.slug));
        });
        this.availableTags = Array.from(tagsSet).sort();
      },
      error: (err) => {
        console.error('Erro ao carregar tags:', err);
      }
    });
  }

  calcularContagemPorTipo(): void {
    if (this.countsLoaded) {
      return;
    }
    
    this.recursoService.listarRecursos(1, 10000).subscribe({
      next: (response) => {
        this.videoCount = response.data.filter(r => r.tipo === 'video').length;
        this.pdfCount = response.data.filter(r => r.tipo === 'pdf').length;
        this.linkCount = response.data.filter(r => r.tipo === 'link').length;
        this.countsLoaded = true;
      },
      error: (err) => {
        console.error('Erro ao calcular contagem por tipo:', err);
      }
    });
  }

  excluirRecurso(id: number, titulo: string): void {
    if (!confirm(`Tem certeza que deseja excluir "${titulo}"?`)) {
      return;
    }

    this.recursoService.excluirRecurso(id).subscribe({
      next: () => {
        this.carregarRecursos();
      },
      error: (err) => {
        this.error = 'Erro ao excluir recurso. Tente novamente.';
        console.error(err);
      }
    });
  }

  proximaPagina(): void {
    if (this.currentPage < this.totalPages) {
      this.currentPage++;
      this.carregarRecursos();
    }
  }

  paginaAnterior(): void {
    if (this.currentPage > 1) {
      this.currentPage--;
      this.carregarRecursos();
    }
  }

  toggleRecurso(id: number): void {
    if (this.expandedRecursos.has(id)) {
      this.expandedRecursos.delete(id);
    } else {
      this.expandedRecursos.add(id);
    }
  }

  isExpanded(id: number): boolean {
    return this.expandedRecursos.has(id);
  }

  trackByRecursoId(index: number, recurso: Recurso): number {
    return recurso.id;
  }

  trackById(index: number, item: any): number {
    return item.id;
  }

  getIconForTipo(tipo: string): any {
    switch (tipo) {
      case 'video':
        return this.VideoIcon;
      case 'pdf':
        return this.FileTextIcon;
      default:
        return this.Link2Icon;
    }
  }

  onSearch(): void {
    this.currentPage = 1;
    this.carregarRecursos();
  }

  irParaPagina(page: number): void {
    this.currentPage = page;
    this.carregarRecursos();
  }

  getPageNumbers(): number[] {
    const pages: number[] = [];
    const maxPagesToShow = 5;
    
    if (this.totalPages <= maxPagesToShow) {
      for (let i = 1; i <= this.totalPages; i++) {
        pages.push(i);
      }
    } else {
      if (this.currentPage <= 3) {
        for (let i = 1; i <= maxPagesToShow; i++) {
          pages.push(i);
        }
      } else if (this.currentPage >= this.totalPages - 2) {
        for (let i = this.totalPages - maxPagesToShow + 1; i <= this.totalPages; i++) {
          pages.push(i);
        }
      } else {
        for (let i = this.currentPage - 2; i <= this.currentPage + 2; i++) {
          pages.push(i);
        }
      }
    }
    
    return pages;
  }

  toggleFilters(): void {
    this.showFilters = !this.showFilters;
  }

  limparFiltros(): void {
    this.filterTipo = '';
    this.filterTags = [];
    this.currentPage = 1;
    this.carregarRecursos();
  }

  aplicarFiltros(): void {
    this.currentPage = 1;
    this.carregarRecursos();
  }

  toggleTagFilter(tag: string): void {
    const index = this.filterTags.indexOf(tag);
    if (index > -1) {
      this.filterTags.splice(index, 1);
    } else {
      this.filterTags.push(tag);
    }
    this.aplicarFiltros();
  }

  isTagSelected(tag: string): boolean {
    return this.filterTags.includes(tag);
  }

  hasActiveFilters(): boolean {
    return this.filterTipo !== '' || this.filterTags.length > 0;
  }
}
