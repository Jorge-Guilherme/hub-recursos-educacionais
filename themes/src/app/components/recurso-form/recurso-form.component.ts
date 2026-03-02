import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule, FormControl } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { RecursoService } from '../../services/recurso.service';
import { RecursoTipo } from '../../models/recurso.model';
import { LucideAngularModule, Sparkles, Loader2, Tag, X, Save } from 'lucide-angular';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
  selector: 'app-recurso-form',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './recurso-form.component.html',
  styleUrls: ['./recurso-form.component.css']
})
export class RecursoFormComponent implements OnInit {
  recursoForm: FormGroup;
  isEditMode = false;
  recursoId: number | null = null;
  loading = false;
  error: string | null = null;
  tagInput = new FormControl('');
  tags: string[] = [];
  gerandoDescricao = false;
  gerandoTags = false;
  availableTags: string[] = [];
  suggestedTags: string[] = [];
  showSuggestions = false;
  recommendedTags: string[] = [];

  readonly SparklesIcon = Sparkles;
  readonly Loader2Icon = Loader2;
  readonly TagIcon = Tag;
  readonly XIcon = X;
  readonly SaveIcon = Save;

  tiposDisponiveis: RecursoTipo[] = ['video', 'pdf', 'link'];

  constructor(
    private fb: FormBuilder,
    private recursoService: RecursoService,
    private router: Router,
    private route: ActivatedRoute
  ) {
    this.recursoForm = this.fb.group({
      titulo: ['', [Validators.required, Validators.maxLength(255)]],
      descricao: ['', [Validators.required]],
      tipo: ['video', [Validators.required]],
      url: ['', [Validators.required, Validators.pattern(/^https?:\/\/.+/)]]
    });
  }

  ngOnInit(): void {
    this.route.params.subscribe(params => {
      const id = params['id'];
      if (id) {
        this.isEditMode = true;
        this.recursoId = +id;
        this.carregarRecurso(this.recursoId);
      }
    });

    // Carregar tags disponíveis
    this.carregarTagsDisponiveis();

    // Configurar sugestões de tags com debounce (no input de tag)
    this.tagInput.valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged()
    ).subscribe(value => {
      this.filtrarSugestoes(value || '');
    });

    // Configurar recomendações de tags baseadas no título
    this.recursoForm.get('titulo')?.valueChanges.pipe(
      debounceTime(500),
      distinctUntilChanged()
    ).subscribe(titulo => {
      this.sugerirTagsPorTitulo(titulo || '');
    });
  }

  carregarRecurso(id: number): void {
    this.loading = true;
    this.recursoService.buscarRecursoPorId(id).subscribe({
      next: (recurso) => {
        this.recursoForm.patchValue({
          titulo: recurso.titulo,
          descricao: recurso.descricao,
          tipo: recurso.tipo,
          url: recurso.url
        });
        this.tags = recurso.tags.map(t => t.nome);
        this.loading = false;
      },
      error: (err) => {
        this.error = 'Erro ao carregar recurso.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  adicionarTag(): void {
    const tag = this.tagInput.value?.trim();
    if (tag && !this.tags.includes(tag)) {
      this.tags.push(tag);
      this.tagInput.setValue('');
    }
  }

  removerTag(tag: string): void {
    this.tags = this.tags.filter(t => t !== tag);
  }

  carregarTagsDisponiveis(): void {
    // Carregar uma amostra de recursos para extrair tags existentes
    this.recursoService.listarRecursos(1, 500).subscribe({
      next: (response) => {
        const tagsSet = new Set<string>();
        response.data.forEach(recurso => {
          recurso.tags.forEach(tag => tagsSet.add(tag.nome));
        });
        this.availableTags = Array.from(tagsSet).sort();
      },
      error: (err) => {
        console.error('Erro ao carregar tags disponíveis:', err);
      }
    });
  }

  filtrarSugestoes(input: string): void {
    const trimmedInput = input.trim().toLowerCase();
    
    if (trimmedInput.length < 2) {
      this.suggestedTags = [];
      this.showSuggestions = false;
      return;
    }

    // Filtrar tags que começam com o input ou contêm o input
    this.suggestedTags = this.availableTags
      .filter(tag => {
        const tagLower = tag.toLowerCase();
        // Não sugerir tags que já foram adicionadas
        if (this.tags.includes(tag)) return false;
        // Priorizar tags que começam com o input
        return tagLower.startsWith(trimmedInput) || tagLower.includes(trimmedInput);
      })
      .slice(0, 8); // Limitar a 8 sugestões
    
    this.showSuggestions = this.suggestedTags.length > 0;
  }

  selecionarSugestao(tag: string): void {
    if (!this.tags.includes(tag)) {
      this.tags.push(tag);
    }
    this.tagInput.setValue('');
    this.suggestedTags = [];
    this.showSuggestions = false;
  }

  ocultarSugestoes(): void {
    // Pequeno delay para permitir clique na sugestão
    setTimeout(() => {
      this.showSuggestions = false;
    }, 200);
  }

  sugerirTagsPorTitulo(titulo: string): void {
    const tituloTrimmed = titulo.trim().toLowerCase();
    
    if (tituloTrimmed.length < 3) {
      this.recommendedTags = [];
      return;
    }

    // Extrair palavras-chave do título (palavras com 3+ caracteres)
    const palavrasChave = tituloTrimmed
      .split(/\s+/)
      .filter(palavra => palavra.length >= 3)
      .map(palavra => palavra.replace(/[^\w]/g, ''));

    if (palavrasChave.length === 0) {
      this.recommendedTags = [];
      return;
    }

    // Buscar tags que contenham qualquer palavra-chave
    const tagsRelevantes = new Set<string>();
    
    this.availableTags.forEach(tag => {
      const tagLower = tag.toLowerCase();
      // Não sugerir tags já adicionadas
      if (this.tags.includes(tag)) return;
      
      // Verificar se a tag contém alguma palavra-chave ou vice-versa
      for (const palavra of palavrasChave) {
        if (tagLower.includes(palavra) || palavra.includes(tagLower)) {
          tagsRelevantes.add(tag);
          break;
        }
      }
    });

    // Limitar a 6 sugestões
    this.recommendedTags = Array.from(tagsRelevantes).slice(0, 6);
  }

  adicionarTagRecomendada(tag: string): void {
    if (!this.tags.includes(tag)) {
      this.tags.push(tag);
      // Remover da lista de recomendadas
      this.recommendedTags = this.recommendedTags.filter(t => t !== tag);
    }
  }

  onTagInputKeyPress(event: KeyboardEvent): void {
    if (event.key === 'Enter') {
      event.preventDefault();
      this.adicionarTag();
    }
  }

  salvar(): void {
    if (this.recursoForm.invalid) {
      Object.keys(this.recursoForm.controls).forEach(key => {
        this.recursoForm.get(key)?.markAsTouched();
      });
      return;
    }

    this.loading = true;
    this.error = null;

    const formData = {
      ...this.recursoForm.value,
      tags: this.tags
    };

    const request$ = this.isEditMode && this.recursoId
      ? this.recursoService.atualizarRecurso(this.recursoId, formData)
      : this.recursoService.criarRecurso(formData);

    request$.subscribe({
      next: () => {
        this.router.navigate(['/']);
      },
      error: (err) => {
        this.error = 'Erro ao salvar recurso. Verifique os dados e tente novamente.';
        this.loading = false;
        console.error(err);
      }
    });
  }

  cancelar(): void {
    this.router.navigate(['/']);
  }

  getFieldError(field: string): string {
    const control = this.recursoForm.get(field);
    if (control?.hasError('required')) {
      return 'Este campo é obrigatório';
    }
    if (control?.hasError('maxlength')) {
      return 'Máximo de 255 caracteres';
    }
    if (control?.hasError('pattern')) {
      return 'URL inválida (deve começar com http:// ou https://)';
    }
    return '';
  }

  isFieldInvalid(field: string): boolean {
    const control = this.recursoForm.get(field);
    return !!(control && control.invalid && control.touched);
  }

  podeGerarDescricao(): boolean {
    const tituloValido = this.recursoForm.get('titulo')?.valid;
    const tipoValido = this.recursoForm.get('tipo')?.valid;
    return !!(tituloValido && tipoValido);
  }

  gerarDescricao(): void {
    if (!this.podeGerarDescricao() || this.gerandoDescricao) {
      return;
    }

    this.gerandoDescricao = true;
    this.error = null;

    const titulo = this.recursoForm.get('titulo')?.value;
    const tipo = this.recursoForm.get('tipo')?.value;
    const url = this.recursoForm.get('url')?.value || null;

    this.recursoService.gerarDescricao(titulo, tipo, url).subscribe({
      next: (response) => {
        this.recursoForm.patchValue({
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

  podeGerarTags(): boolean {
    const tituloValido = this.recursoForm.get('titulo')?.valid;
    const tipoValido = this.recursoForm.get('tipo')?.valid;
    return !!(tituloValido && tipoValido);
  }

  gerarTags(): void {
    if (!this.podeGerarTags() || this.gerandoTags) {
      return;
    }

    this.gerandoTags = true;
    this.error = null;

    const titulo = this.recursoForm.get('titulo')?.value;
    const tipo = this.recursoForm.get('tipo')?.value;
    const descricao = this.recursoForm.get('descricao')?.value || null;

    this.recursoService.gerarTags(titulo, tipo, descricao).subscribe({
      next: (response) => {
        response.tags.forEach(tag => {
          if (!this.tags.includes(tag)) {
            this.tags.push(tag);
          }
        });
        this.gerandoTags = false;
      },
      error: (err) => {
        const message = err.error?.message || 'Erro ao gerar tags com IA. Tente novamente.';
        this.error = message;
        this.gerandoTags = false;
        console.error('Erro ao gerar tags:', err);
      }
    });
  }
}
