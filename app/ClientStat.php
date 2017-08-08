<?php

namespace App;


use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
class ClientStat extends Eloquent
{
    protected $connection = 'mongodb';

    protected $fillable=[
        "mac",
        "lastip",
        "signal",
        "rssi",
        "noisefloor".
        "chainrssi",
        "tx_idx",
        "rx_idx",
        "tx_nss",
        "rx_nss",
        "tx_latency",
        "distance",
        "tx_packets",
        "tx_lretries",
        "tx_sretries",
        "uptime",
        "stats",
        "remote",
        "airmax",
    ];


}
