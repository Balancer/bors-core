#!/bin/bash

hg bookmark -r default master
hg push git+ssh://git@github.com:Balancer/bors-core.git
