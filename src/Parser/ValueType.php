<?php

declare(strict_types=1);

namespace HypnoTox\Toml\Parser;

enum ValueType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Bool = 'bool';
    case OffsetDateTime = 'datetime';
    case LocalDateTime = 'datetime-local';
    case LocalDate = 'date-local';
    case LocalTime = 'time-local';
}
