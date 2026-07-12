<?php

namespace App\DTOs\Auth;

use Illuminate\Http\Request;

class RegisterDTO
{
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            first_name: $request->input('first_name'),
            last_name: $request->input('last_name'),
            email: $request->input('email'),
            password: $request->input('password')
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
