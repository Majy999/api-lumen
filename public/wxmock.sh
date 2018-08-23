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
    nohup  php /data/web/api.majy999.com/artisan wxmock $data  &
}


data=$(echo "rPop wxmock" | $RedisBin  -h $RedisIpaddr -a $RedisPassword)

data_lenth=$(echo -n $data|wc -c)
if [ $data_lenth -eq 0 ]; then
    #echo  "没有数据"
    exit -1;
else
    check_exist_process_run
fi

