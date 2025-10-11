<?php

namespace Feenstra\CMS\Pagebuilder\Support;

use Feenstra\CMS\Pagebuilder\Traits\HasLink;

class Button {
    use HasLink;

    public function __construct(array $data = []) {
        $this->link = new Link(@$data['link']);
    }
}
