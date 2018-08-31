#!/bin/bash
RedisIpaddr=127.0.0.1
RedisPassword='123456'
RedisBin='redis-cli'

check_exist_process_run() {
    exist_process_num=$(ps -elf | grep wxmock| grep -v grep| grep $data|wc -l)
        if [ $exist_process_num -ge 1 ]; then
            pid=$(ps -elf | grep wxmock| grep -v grep| grep $data|awk '{print $4}')
            echo "存在进程！"
            kill -9 $pid
        fi
         echo "data_start:$data"
         php /data/wwwroot/api.majy999.com/artisan wxmock $data >/dev/null &
         echo "data_end:$data"
    }



    while true; do
    	data=$(echo "rPop wxmock" | $RedisBin  -h $RedisIpaddr -a $RedisPassword 2>/dev/null)
    	data_lenth=$(echo -n $data|wc -c)
    	if [ $data_lenth -eq 0 ]; then
        		echo  "没有数据"
        		#exit -1;
    	else
    		echo  "接收数据:$data"
        		check_exist_process_run
    	fi
    	sleep 1;
    done





