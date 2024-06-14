<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Game;

class GameController extends Controller
{
    private const API_ENDPOINT = 'https://api.sportsdata.io/v3';

    public function index()
    {
        $this->initializeSeason();
        $games = Game::all(['game_date', 'home_team', 'away_team']);
        $events = $games->map(function($game) {
            return [
                'title' => $game->home_team . ' vs ' . $game->away_team,
                'start' => $game->game_date,
            ];
        })->unique('start')->values()->all();
        return view('games.index', ['events' => $events]);
    }

    public function fetchGames(Request $request)
    {
        $date = $request->query('date');
        $games = Game::whereDate('game_date', $date)->get();

        if ($games->isEmpty()) {
            $games = $this->getGamesFromApi($date);
            $this->storeGames($games);
        } else {
            $games = $games->toArray();
        }

        return response()->json($this->formatGames($games));
    }

    public function initializeSeason($season = '2024POST')
    {
        $apiKey = config('services.sports-data.key');

        $response = Http::get(self::API_ENDPOINT . "/nba/scores/json/SchedulesBasic/$season", [
            'key' => $apiKey,
        ]);

        if ($response->successful()) {
            $games = $response->json();
            $this->storeGames($games);
        }
    }

    private function getGamesFromApi($date)
    {
        $apiKey = config('services.sports-data.key');
        $response = Http::get(self::API_ENDPOINT . "/nba/scores/json/ScoresBasic/$date", [
            'key' => $apiKey,
        ]);

        if ($response->successful()) {
            $games = $response->json();
            return $this->formatGames($games);
        }

        return [];
    }

    private function formatGames($games)
    {
        return array_map(function ($game) {
            $homeScore = $game['HomeTeamScore'] ?? $game['home_score'] ?? 'TBD';
            $awayScore = $game['AwayTeamScore'] ?? $game['away_score'] ?? 'TBD';
            $homeTeam = $game['HomeTeam'] ?? $game['home_team'];
            $awayTeam = $game['AwayTeam'] ?? $game['away_team'];
            $gameDate = $game['DateTime'] ?? $game['game_date'] ?? '';
            if ($homeScore === 'TBD') {
                $winningTeam = 'TBD';
            } else {
                $winningTeam = $homeScore > $awayScore ? $homeTeam : $awayTeam;
            }
            return [
                'game_date' => $gameDate,
                'home_team' => $homeTeam,
                'away_team' => $awayTeam,
                'home_score' => $homeScore,
                'away_score' => $awayScore,
                'winning_team' => $winningTeam,
                'title' => $homeTeam . ' vs ' . $awayTeam,
                'start' => $gameDate,
            ];
        }, $games);
    }

    private function storeGames($games)
    {
        foreach ($games as $game) {
            if (!isset($game['DateTime'])) continue;
            Game::updateOrCreate(
                [
                    'game_date' => $game['DateTime'],
                    'home_team' => $game['HomeTeam'],
                    'away_team' => $game['AwayTeam'],
                ],
                [
                    'home_score' => $game['HomeTeamScore'],
                    'away_score' => $game['AwayTeamScore'],
                ]
            );
        }
    }
}
