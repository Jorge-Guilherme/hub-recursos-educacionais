import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { GrupoService } from '../../services/grupo.service';
import { RecursoService } from '../../services/recurso.service';
import { Grupo } from '../../models/grupo.model';
import { Recurso } from '../../models/recurso.model';
import { LucideAngularModule, Save, X, Check, Square, Sparkles, Loader2 } from 'lucide-angular';

@Component({
  selector: 'app-grupo-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './grupo-form.component.html',
  styleUrls: ['./grupo-form.component.css']
})
export class GrupoFormComponent implements OnInit {
  grupoForm: FormGroup;
  isEditMode = false;
  grupoId: number | null = null;
  loading = false;
  error: string | null = null;
  successMessage: string | null = null;
  gerandoDescricao = false;

  todosRecursos: Recurso[] = [];
  recursosSelecionados: Set<number> = new Set();
  loadingRecursos = false;

  readonly SaveIcon = Save;
  readonly XIcon = X;
  readonly CheckIcon = Check;
  readonly SquareIcon = Square;
  readonly SparklesIcon = Sparkles;
  readonly Loader2Icon = Loader2;

  constructor(
    private fb: FormBuilder,
    private grupoService: GrupoService,
    private recursoService: RecursoService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.grupoForm = this.fb.group({
      nome: ['', [Validators.required, Validators.maxLength(255)]],
      descricao: ['']
    });
  }

  ngOnInit(): void {
    this.carregarRecursos();
    
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.isEditMode = true;
        this.grupoId = +id;
        this.carregarGrupo(this.grupoId);
      }
    });
  }

  carregarGrupo(id: number): void {
    this.loading = true;
    this.grupoService.buscarGrupoPorId(id, true).subscribe({
      next: (grupo) => {
        this.grupoForm.patchValue({
          nome: grupo.nome,
          descricao: grupo.descricao
        });
        
        if (grupo.recursos) {
          grupo.recursos.forEach(r => this.recursosSelecionados.add(r.id));
        }
        
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erro ao carregar grupo. Tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  carregarRecursos(): void {
    this.loadingRecursos = true;
    this.recursoService.listarRecursos(1, 1000).subscribe({
      next: (response) => {
        this.todosRecursos = response.data;
        this.loadingRecursos = false;
      },
      error: (err) => {
        console.error('Erro ao carregar recursos:', err);
        this.loadingRecursos = false;
      }
    });
  }

  podeGerarDescricao(): boolean {
    const nomeValido = this.grupoForm.get('nome')?.valid;
    return !!nomeValido;
  }

  gerarDescricao(): void {
    if (!this.podeGerarDescricao() || this.gerandoDescricao) {
      return;
    }

    this.gerandoDescricao = true;
    this.error = null;

    const nome = this.grupoForm.get('nome')?.value;
    this.grupoService.gerarDescricao(nome).subscribe({
      next: (response) => {
        this.grupoForm.patchValue({
          descricao: response.descricao
        });
        this.gerandoDescricao = false;
      },
      error: (err) => {
        const message = err.error?.message || 'Erro ao gerar descrição com IA. Tente novamente.';
        this.error = message;
        this.gerandoDescricao = false;
        console.error('Erro ao gerar descrição:', err);
      }
    });
  }

  toggleRecurso(recursoId: number): void {
    if (this.recursosSelecionados.has(recursoId)) {
      this.recursosSelecionados.delete(recursoId);
    } else {
      this.recursosSelecionados.add(recursoId);
    }
  }

  isRecursoSelecionado(recursoId: number): boolean {
    return this.recursosSelecionados.has(recursoId);
  }

  trackByRecursoId(index: number, recurso: Recurso): number {
    return recurso.id;
  }

  onSubmit(): void {
    if (this.grupoForm.invalid) {
      Object.keys(this.grupoForm.controls).forEach(key => {
        this.grupoForm.get(key)?.markAsTouched();
      });
      return;
    }

    this.loading = true;
    this.error = null;
    this.successMessage = null;

    const formData = {
      ...this.grupoForm.value,
      recurso_ids: Array.from(this.recursosSelecionados)
    };

    const request = this.isEditMode && this.grupoId
      ? this.grupoService.atualizarGrupo(this.grupoId, formData)
      : this.grupoService.criarGrupo(formData);

    request.subscribe({
      next: () => {
        this.successMessage = this.isEditMode ? 'Grupo atualizado com sucesso!' : 'Grupo criado com sucesso!';
        this.loading = false;
        setTimeout(() => {
          this.router.navigate(['/'], { queryParams: { tab: 'grupos' } });
        }, 1500);
      },
      error: (err) => {
        this.error = 'Erro ao salvar grupo. Verifique os dados e tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  cancelar(): void {
    this.router.navigate(['/'], { queryParams: { tab: 'grupos' } });
  }

  isFieldInvalid(fieldName: string): boolean {
    const field = this.grupoForm.get(fieldName);
    return !!(field && field.invalid && field.touched);
  }

  getFieldError(fieldName: string): string {
    const field = this.grupoForm.get(fieldName);
    if (field?.errors) {
      if (field.errors['required']) return 'Este campo é obrigatório';
      if (field.errors['maxlength']) return `Máximo de ${field.errors['maxlength'].requiredLength} caracteres`;
    }
    return '';
  }
}
