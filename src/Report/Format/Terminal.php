<?php

namespace Drutiny\Report\Format;

use Drutiny\Profile;
use Drutiny\AssessmentInterface;
use Fiasco\SymfonyConsoleStyleMarkdown\Renderer;
use Symfony\Component\Yaml\Yaml;

class Terminal extends Markdown
{
    protected $format = 'terminal';

    public function render(Profile $profile, AssessmentInterface $assessment)
    {
        parent::render($profile, $assessment);
        $this->buffer->write(Renderer::createFromMarkdown($this->buffer->fetch()));
        return $this;
    }
}
