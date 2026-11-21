<!DOCTYPE html>
<html lang="lt">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $day?->date }}</title>
    <style>
        body {
            font-family: DejaVu Sans;
            font-size: 12px;
        }
        table {
            border-collapse: collapse;
        }
        td {
            border: 1px solid #000000;
            padding: 10px;
        }
        thead td, tfoot td {
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <p>
        <b>{{ __('general.eater') }}:</b> {{ $day->eater->name ?? '' }}
    </p>
    <p>
        <b>{{ __('general.day') }}:</b> {{ $day?->date }}
    </p>
    @foreach($day->mealtimes as $mealtime)
        <h2>{{ $mealtime->translation->translation ?? '' }}</h2>
        @if($mealtime->dishes)
            @foreach($mealtime->dishes as $dish)
                <h3>{{ $dish->translation->translation ?? '' }}</h3>
                <table>
                    <thead>
                        <tr>
                            <td>
                                {{ __('general.ingredient') }}
                            </td>
                            <td>
                                {{ __('general.mass_brutto') }}
                            </td>
                            <td>
                                {{ __('general.yield') }}
                            </td>
                            <td>
                                {{ __('general.proteins') }}
                            </td>
                            <td>
                                {{ __('general.fat') }}
                            </td>
                            <td>
                                {{ __('general.carbohydrates') }}
                            </td>
                            <td>
                                {{ __('general.kilocalories') }}
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dish->ingredients as $ingredient)
                            <tr>
                                <td>
                                    {{ $ingredient->translation->translation ?? '' }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($ingredient->pivot->mass_brutto ?? 0, 0, ',') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format($ingredient->pivot->mass_netto ?? 0, 0, ',') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format(($ingredient->pivot->mass_netto ?? 0) * $ingredient->proteins, 2, ',') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format(($ingredient->pivot->mass_netto ?? 0) * $ingredient->fat, 2, ',') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format(($ingredient->pivot->mass_netto ?? 0) * $ingredient->carbohydrates, 2, ',') }}
                                </td>
                                <td class="text-right">
                                    {{ number_format(($ingredient->pivot->mass_netto ?? 0) * $ingredient->calories, 2, ',') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>
                                {{ __('general.total') }}:
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['mass_brutto'] ?? 0, 0, ',') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['mass_netto'] ?? 0, 0, ',') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['proteins'], 2, ',') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['fat'], 2, ',') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['carbohydrates'], 2, ',') }}
                            </td>
                            <td class="text-right">
                                {{ number_format($day->sum_nutrition_values['dishes'][$dish->id]['calories'], 2, ',') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach
        @endif
        <hr/>
    @endforeach

    <h3>{{ __('general.total') }}</h3>
    <table>
        <thead>
        <tr>
            <td>
                {{ __('general.mass_brutto') }}
            </td>
            <td>
                {{ __('general.yield') }}
            </td>
            <td>
                {{ __('general.proteins') }}
            </td>
            <td>
                {{ __('general.fat') }}
            </td>
            <td>
                {{ __('general.carbohydrates') }}
            </td>
            <td>
                {{ __('general.kilocalories') }}
            </td>
        </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['mass_brutto'] ?? 0, 0, ',') }}
                </td>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['mass_netto'] ?? 0, 0, ',') }}
                </td>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['proteins'] ?? 0, 2, ',') }}
                </td>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['fat'] ?? 0, 2, ',') }}
                </td>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['carbohydrates'] ?? 0, 2, ',') }}
                </td>
                <td class="text-right">
                    {{ number_format($day->sum_nutrition_values['total']['calories'] ?? 0, 2, ',', '') }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>