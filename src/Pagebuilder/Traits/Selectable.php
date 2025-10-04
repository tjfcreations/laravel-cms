<?php
    namespace Feenstra\CMS\Pagebuilder\Traits;

    use Illuminate\Support\Str;

    trait Selectable {
        public function getLabel() {
            if(isset($this->label)) return $this->label;
            if(isset($this->name)) return $this->name;
            if(isset($this->title)) return $this->title;
            if(isset($this->slug)) return $this->slug;
            return $this->id;
        }

        public function getGroupLabel() {
            return Str::plural(class_basename($this));
        }
    }