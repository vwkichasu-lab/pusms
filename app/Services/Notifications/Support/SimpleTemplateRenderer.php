<?php

namespace App\Services\Notifications\Support;

final class SimpleTemplateRenderer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function renderText(string $template, array $data): string
    {
        return $this->replace($template, $data, false);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function renderHtml(string $template, array $data): string
    {
        return $this->replace($template, $data, true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function replace(string $template, array $data, bool $escape): string
    {
        foreach ($data as $key => $value) {
            $replacement = (string) $value;

            if ($escape) {
                $replacement = e($replacement);
            }

            $template = str_replace('{{'.$key.'}}', $replacement, $template);
        }

        return $template;
    }
}
