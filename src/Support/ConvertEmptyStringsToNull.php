<?php

namespace PressbooksMultiInstitution\Support;

class ConvertEmptyStringsToNull
{
    public function handle(array $data): array
    {
        return $this->cleanArray($data);
    }

    protected function cleanArray(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->cleanValue($value);
        }

        return $data;
    }

    protected function cleanValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return $this->cleanArray($value);
        }

        return $this->transform($value);
    }

    protected function transform(mixed $value): ?string
    {
        return $value === '' ? null : sanitize_text_field($value);
    }
}
