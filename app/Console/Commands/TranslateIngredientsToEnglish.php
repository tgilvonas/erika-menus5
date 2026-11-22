<?php

namespace App\Console\Commands;

use App\Models\IngredientTranslation;
use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Ai\AiManager;
use Laravel\Ai\AnonymousAgent;

class TranslateIngredientsToEnglish extends Command
{
    protected $signature = 'ingredients:translate-en';

    protected $description = 'Translate Lithuanian ingredient names to English with the first configured user Gemini API key';

    public function handle(AiManager $aiManager): int
    {
        $user = User::query()
            ->whereNotNull('google_gemini_api_key')
            ->where('google_gemini_api_key', '!=', '')
            ->orderBy('id')
            ->first();

        if (! $user || blank($user->google_gemini_api_key)) {
            $this->error('No user with a configured Google Gemini API key was found.');

            return self::FAILURE;
        }

        config()->set('ai.providers.gemini.key', $user->google_gemini_api_key);
        $aiManager->forgetInstance('gemini');

        $agent = new AnonymousAgent(
            'You translate Lithuanian ingredient names to English. Return only the English translation. Use the common culinary or grocery name, without explanations, quotes, or extra text.',
            [],
            [],
        );

        $translations = IngredientTranslation::query()
            ->where('lang', 'lt')
            ->whereNotNull('translation')
            ->where('translation', '!=', '')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('ingredients_translations as en_translation')
                    ->whereColumn('en_translation.ingredient_id', 'ingredients_translations.ingredient_id')
                    ->where('en_translation.lang', 'en')
                    ->whereNotNull('en_translation.translation')
                    ->where('en_translation.translation', '!=', '');
            })
            ->orderBy('ingredient_id')
            ->get();

        if ($translations->isEmpty()) {
            $this->warn('No Lithuanian ingredient translations were found.');

            return self::SUCCESS;
        }

        $this->withProgressBar($translations, function (IngredientTranslation $translation) use ($agent) {
            try {
                $response = $agent->prompt(
                    'Translate this Lithuanian ingredient name to English: '.$translation->translation,
                    provider: 'gemini',
                );

                $english = trim($response->text);
                $english = trim($english, " \t\n\r\0\x0B\"'");

                if ($english !== '') {
                    IngredientTranslation::updateOrCreate(
                        [
                            'ingredient_id' => $translation->ingredient_id,
                            'lang' => 'en',
                        ],
                        ['translation' => mb_substr($english, 0, 250)],
                    );
                }

                sleep(15);
            } catch (\Throwable $exception) {
                $this->newLine();
                $this->error("Ingredient {$translation->ingredient_id}: {$exception->getMessage()}");
            }
        });

        $this->newLine();
        $this->info('Ingredient translations processed.');

        return self::SUCCESS;
    }
}
