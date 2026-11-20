<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="utf-8">
    <title>{{ __('general.day') }} {{ $day->date }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #777; padding: 6px; text-align: left; }
        th { background: #eee; }
        .totals td { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ __('general.day') }} {{ $day->date }}</h1>

    @if($day->eater)
        <p><strong>{{ __('general.eater') }}:</strong> {{ $day->eater->name }}</p>
    @endif

    @foreach($day->mealtimes as $mealtime)
        <h2>{{ optional($mealtime->translation)->translation ?? $mealtime->name ?? '' }}</h2>
        <table>
            <thead>
                <tr>
                    <th>{{ __('general.dish') }}</th>
                    <th>{{ __('general.mass_brutto') }}</th>
                    <th>{{ __('general.mass_netto') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mealtime->dishes as $dish)
                    <tr>
                        <td>{{ optional($dish->translation)->translation ?? $dish->name ?? '' }}</td>
                        <td>{{ $dish->pivot->mass_brutto ?? 0 }}</td>
                        <td>{{ $dish->pivot->mass_netto ?? 0 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @php($total = $day->sum_nutrition_values['total'] ?? [])
    <table class="totals">
        <thead><tr><th colspan="2">{{ __('general.total') }}</th></tr></thead>
        <tbody>
            <tr><td>{{ __('general.proteins') }}</td><td>{{ number_format($total['proteins'] ?? 0, 2) }}</td></tr>
            <tr><td>{{ __('general.fat') }}</td><td>{{ number_format($total['fat'] ?? 0, 2) }}</td></tr>
            <tr><td>{{ __('general.carbohydrates') }}</td><td>{{ number_format($total['carbohydrates'] ?? 0, 2) }}</td></tr>
            <tr><td>{{ __('general.calories') }}</td><td>{{ number_format($total['calories'] ?? 0, 2) }}</td></tr>
        </tbody>
    </table>
</body>
</html>
