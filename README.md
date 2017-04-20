# TestOpenRedirect
PHP tool to test Open Redirect.  
Note that this is an automated tool, manual check is still required.  

```
Usage: php ultimate-open-redirect.php [OPTIONS] -t <target> -z <hacker url>

Options:
	-h	print this help
	-e	set threads, default=5
	-i	set timeout, default=10
	-r	do NOT follow redirection
	-t	single target to test or source file
	-u	urlencode payloads, default=false
	-z	hacker url

Examples:
	php ultimate-open-redirect.php -t http://www.example.com -z 10degres.net
	php ultimate-open-redirect.php -u -r -t target.txt -z 10degres.net
```

I don't believe in license.  
You can do want you want with this program.  
