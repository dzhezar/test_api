<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("api/leagues")
     */
    public function list(Request $request): JsonResponse
    {
        $data = json_decode($this->client->request('GET',
            'https://www.dota2.com/webapi/IDOTA2League/GetLeagueInfoList/v001')->getContent(), true)['infos'];
        $params = $request->query->all();
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                switch ($param) {
                    case 'start_timestamp':
                        $data = array_filter($data, function ($event) use ($param, $value) {
                            return $event[$param] >= $value;
                        });
                        break;
                    case 'end_timestamp':
                        $data = array_filter($data, function ($event) use ($param, $value) {
                            return $event[$param] <= $value;
                        });
                        break;
                }
            }
        }
        return new JsonResponse($data);
    }

    /**
     * @Route("api/leagues/{leagueId}")
     */
    public function findEvent(int $leagueId): JsonResponse
    {
        $data = json_decode($this->client->request('GET',
            'https://www.dota2.com/webapi/IDOTA2League/GetLeagueInfoList/v001')->getContent(), true)['infos'];
        $leagueIndex = array_search($leagueId, array_column($data, 'league_id'));
        $leagueName = is_numeric($leagueIndex) ?
            $data[$leagueIndex]['name'] :
            'Invalid League ID provided';
        return new JsonResponse(['name' => $leagueName]);
    }
}