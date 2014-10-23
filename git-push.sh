#!/bin/bash

hg bookmark -r default master
hg push git+ssh://git@github.com:Balancer/bors-core.git


# git push -f --set-upstream git@github.com:Balancer/bors-core.git master
