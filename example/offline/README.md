## Offline example

run the files in the following order:

`php bob1.php` 

`php alice1.php <output from bob1.php>`

`php bob2.php <output from alice1.php>`

The key agreement is now complete, and you can run the following files in 
any order to send/receive messages

```
php bob-send.php <plaintext of message>
php bob-receive.php <output from alice-send.php>

php alice-send.php <plaintext of message>
php alice-receive.php <output from bob-send.php>
```

To reset the example, delete the files `bob-state` and `alice-state`