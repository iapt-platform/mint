#!/bin/sh

date

php ./migrations/20220211155400_custom_book_copy.php
php ./migrations/20220213092400_custom_book_sentence_copy.php

date