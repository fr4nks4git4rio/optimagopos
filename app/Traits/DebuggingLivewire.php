<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
trait DebuggingLivewire
{
    public function debugSerializableProperties()
    {
        $nonSerializable = [];

        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties(\ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($this);

            // Intenta serializar cada propiedad
            try {
                $serialized = serialize($value);
                $unserialized = unserialize($serialized);

                // Verifica si el unserialized es igual al original
                if ($value !== $unserialized && !is_array($value)) {
                    $nonSerializable[$property->getName()] = [
                        'type' => gettype($value),
                        'class' => is_object($value) ? get_class($value) : 'N/A',
                        'reason' => 'Serialization mismatch'
                    ];
                }
            } catch (\Exception $e) {
                $nonSerializable[$property->getName()] = [
                    'type' => gettype($value),
                    'class' => is_object($value) ? get_class($value) : 'N/A',
                    'reason' => $e->getMessage()
                ];
            }
        }

        if (!empty($nonSerializable)) {
            Log::error('SavePrefactura - Propiedades no-serializables detectadas:', $nonSerializable);
            dd('Propiedades problemáticas encontradas. Ver logs.');
        }

        Log::info('SavePrefactura - Todas las propiedades son serializables ✓');
    }
}
