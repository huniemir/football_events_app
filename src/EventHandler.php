<?php

namespace App;

class EventHandler
{
    private FileStorage $storage;
    private StatisticsManager $statisticsManager;
    private array $config;
    private array $supportedTypes;
    
    public function __construct(string $storagePath, ?StatisticsManager $statisticsManager = null)
    {
        $this->config = require __DIR__ . '/../config/config.php';
        $this->storage = new FileStorage($storagePath);
        $this->statisticsManager = $statisticsManager ?? new StatisticsManager($this->config['storageStatistics']);
        $this->supportedTypes = ['foul','goal'];
    }
    
    public function handleEvent(array $data): array
    {
        if (!isset($data['type'])) {
            throw new \InvalidArgumentException('Event type is required');
        }

        if(!in_array($data['type'],$this->supportedTypes)){
            throw new \InvalidArgumentException('Event type is not supported');
        }

        if (!isset($data['match_id']) || !isset($data['team_id'])) {
            throw new \InvalidArgumentException('match_id and team_id are required for all supported events');
        }

        $validatedData = [];
        $validatedData['type'] = $data['type'];
        $validatedData['match_id'] = $data['match_id'];
        $validatedData['team_id'] = $data['team_id'];

        //Optional arguments
        //minute, second
        if(isset($data['minute'])){
            $minute = filter_var($data['minute'], FILTER_VALIDATE_INT);
            if($minute === false || !($minute >= 0)){
                throw new \InvalidArgumentException('Invalid minute value');
            }
            $validatedData['minute'] = $minute;
        }
        if(isset($data['second'])){
            $second = filter_var($data['second'], FILTER_VALIDATE_INT);
            if($second === false ||!($second < 60 && $second >= 0)){
                throw new \InvalidArgumentException('Invalid second value');
            }
            $validatedData['second'] = $second;
        }

        //scorer, assisting player
        if($data['type'] === 'goal'){
            if(isset($data['scorer'])){
                if(strlen($data['scorer'])>40){
                    throw new \InvalidArgumentException('Invalid scorer value');
                }
                $validatedData['scorer'] = $data['scorer'];
            }
            if(isset($data['assisting_player'])){
                if(strlen($data['assisting_player'])>40){
                    throw new \InvalidArgumentException('Invalid assisting_player value');
                }
                $validatedData['assisting_player'] = $data['assisting_player'];
            }

        }

        //player at fault, affected player
        if($data['type'] === 'foul'){
            if(isset($data['player_at_fault'])){
                if(strlen($data['player_at_fault'])>40){
                    throw new \InvalidArgumentException('Invalid player_at_fault value');
                }
                $validatedData['player_at_fault'] = $data['player_at_fault'];
            }
            if(isset($data['affected_player'])){
                if(strlen($data['affected_player'])>40){
                    throw new \InvalidArgumentException('Invalid affected_player value');
                }
                $validatedData['affected_player'] = $data['affected_player'];
            }
        }
        
        
        
        $event = [
            'type' => $data['type'],
            'timestamp' => time(),
            'data' => $validatedData
        ];
        

        // Update statistics for all supported events

        $this->storage->save($event);
                    
        $this->statisticsManager->updateTeamStatistics(
            $data['match_id'],
            $data['team_id'],
            $data['type'].'s'   //goals, fouls etc.
        );
        
        return [
            'status' => 'success',
            'message' => 'Event saved successfully',
            'event' => $event
        ];
    }
}