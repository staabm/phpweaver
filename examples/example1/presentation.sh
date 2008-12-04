#!/bin/bash
ls -alp --color
source-highlight --out-format=esc --line-number --input foo.php && echo
../../trace.sh foo.php
ls -alp --color
../../weave.php foo.php > foo.out.php
meld foo.php foo.out.php
