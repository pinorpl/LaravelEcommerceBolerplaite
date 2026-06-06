<?php

return [
    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'email'                => 'El campo :attribute debe ser una dirección de correo válida.',
    'max'                  => [
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'min'                  => [
        'string'  => 'El campo :attribute debe tener al menos :min caracteres.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
    ],
    'required'             => 'El campo :attribute es obligatorio.',
    'string'               => 'El campo :attribute debe ser una cadena de texto.',
    'unique'               => 'El :attribute ya está en uso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'numeric'              => 'El campo :attribute debe ser un número.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'boolean'              => 'El campo :attribute debe ser verdadero o falso.',
    'exists'               => 'El :attribute seleccionado no existe.',
    'nullable'             => '',

    'custom' => [],

    'attributes' => [
        'name'                  => 'nombre',
        'email'                 => 'correo electrónico',
        'password'              => 'contraseña',
        'price'                 => 'precio',
        'stock'                 => 'stock',
        'description'           => 'descripción',
        'product_id'            => 'producto',
        'quantity'              => 'cantidad',
        'shipping_address'      => 'dirección de envío',
        'slug'                  => 'identificador URL',
        'is_active'             => 'estado activo',
    ],
];
