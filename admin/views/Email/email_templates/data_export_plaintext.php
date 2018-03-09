<?php

if ($emailObject->data->status === 'COMPLETE') {
    echo 'The data export you requested has been generated; please log in to download.';
} else {
    echo 'The data export you requested failed to generate; the following error was provided: ';
    echo $emailObject->data->error;
}
