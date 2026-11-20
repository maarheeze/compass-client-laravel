<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Http\Requests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ParameterizedRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;
use Webmozart\Assert\Assert;

class StatusRequest extends FormRequest
{
    public function compassApiUrl(): string
    {
        return Assert::string($this->validated('compass_api_url'));
    }

    public function compassApiUrlValidUntil(): CarbonImmutable
    {
        return CarbonImmutable::parse(Assert::string($this->validated('compass_api_url_valid_until')));
    }

    public function hasCompassApiUrl(): bool
    {
        return $this->validated('compass_api_url') !== null;
    }

    /**
     * @return array<string, list<ParameterizedRule|Stringable>>
     */
    public function rules(): array
    {
        return [
            'compass_api_url' => [
                Rule::requiredWith('compass_api_url_valid_until'),
                Rule::string(),
                Rule::url(),
            ],
            'compass_api_url_valid_until' => [
                Rule::requiredWith('compass_api_url'),
                Rule::string(),
                Rule::date(),
            ],
        ];
    }
}
