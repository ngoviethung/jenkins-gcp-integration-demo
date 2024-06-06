#!/bin/bash

kubectl apply -f nfs-server/.

kubectl apply -f volumn/.

kubectl apply -f mysql/.

kubectl apply -f mongo/.

#kubectl apply -f redis/.

#kubectl apply -f rabbit-mq/.

kubectl apply -f php/.

kubectl apply -f nginx/.

#kubectl apply -f cron/.

#kubectl apply -f consumer/.

kubectl apply -f ingress/.






