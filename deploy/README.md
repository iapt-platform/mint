# Deployment

## Setup a cluster

```bash
# create cluster
mkdir -p clients/CLUSTER/.ssh
cd clients/CLUSTER
# append your cluster hosts
touch hosts
# generate ssh key
ssh-keygen -t ed25519 -f .ssh/id_ed25519
# upload the ssh public key to target host
ssh-copy-id -i .ssh/id_ed25519 USER@HOST
```

## Deploy

```bash
# test ssh connections
peony -i staging ping.yml
# run on all hosts
peony -i staging pi.yml
# run on only group
peony -i staging pi.yml -l GROUP
```

## System image

- [Raspberry Pi OS Lite](https://www.raspberrypi.com/software/operating-systems/)
- [Armbian](https://www.armbian.com/download/)
- [wiringPi for Orange Pi](https://github.com/orangepi-xunlong/WiringOP)
