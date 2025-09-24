<?php
    namespace FeenstraDigital\LaravelCMS\Pagebuilder\Support;

    use Illuminate\Support\Collection;
    use FeenstraDigital\LaravelCMS\Pagebuilder\Filament\Forms;

    class QuickSelect {
        public string $model;
        public string $label;
        public ?string $dateAttribute = null;

        public static function for(string $model): static {
            $static = new static($model);
            return $static;
        }

        public function __construct(string $model) {
            $this->model = $model;
        }

        /**
         * Set the label for the inputs.
         */
        public function label(string $label): static {
            $this->label = $label;
            return $this;
        }

        /**
         * Set the attribute used for sorting recent records.
         */
        public function withRecent(string $dateAttribute = 'created_at'): static {
            $this->dateAttribute = is_string($dateAttribute) ? $dateAttribute : null;
            return $this;
        }

        public function getRecords(array $data): Collection {
            switch($data['view']) {
                case 'all':
                    return $this->model::all();
                case 'recent':
                    return $this->model::query()
                        ->orderBy($this->dateAttribute, 'desc')
                        ->limit($data['limit'])
                        ->get();
                case 'selected':
                    return $this->model::whereIn('id', $data['records'])
                        ->get();
                default:
                    return collect();
            }

        }

        public function toFormComponent() {
            return Forms\Components\QuickSelect::make()
                ->model_($this->model)
                ->label($this->label)
                ->dateAttribute($this->dateAttribute);
        }
    }