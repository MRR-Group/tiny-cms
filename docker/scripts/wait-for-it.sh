#!/usr/bin/env bash
#   Use this script to test if a given TCP host/port are available

wait_for()
{
    if [[ $TIMEOUT -gt 0 ]]; then
        echo "Waiting $TIMEOUT seconds for $HOST:$PORT"
    else
        echo "Waiting for $HOST:$PORT without a timeout"
    fi
    start_ts=$(date +%s)
    while :
    do
        if [[ $IS_BUSYBOX -eq 1 ]]; then
            nc -z "$HOST" "$PORT"
            result=$?
        else
            (echo > /dev/tcp/"$HOST"/"$PORT") >/dev/null 2>&1
            result=$?
        fi
        if [[ $result -eq 0 ]]; then
            end_ts=$(date +%s)
            echo "$HOST:$PORT is available after $((end_ts - start_ts)) seconds"
            break
        fi
        sleep 1
    done
    return $result
}

HOST=""
PORT=""
TIMEOUT=15
IS_BUSYBOX=0
if [[ $(command -v nc) && $(nc -h 2>&1 | grep -i busybox) ]]; then
    IS_BUSYBOX=1
fi

while [[ $# -gt 0 ]]
do
    case "$1" in
        *:* )
        HOST=$(echo "$1" | cut -d : -f 1)
        PORT=$(echo "$1" | cut -d : -f 2)
        shift 1
        ;;
        -t | --timeout )
        TIMEOUT="$2"
        shift 2
        ;;
        --busybox )
        IS_BUSYBOX=1
        shift 1
        ;;
        * )
        break
        ;;
    esac
done

if [[ "$HOST" == "" || "$PORT" == "" ]]; then
    echo "Error: you need to provide a host and port to test."
    exit 1
fi

wait_for
