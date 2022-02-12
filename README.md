## What is this?

This is a PHP/Libsodium implementation of the signal X3DH key exchange and double
ratchet protocols.

Unlike Signal, this is designed to work "offline" as in the library provides no
means of communicating with other clients; the idea is that this library will 
perform the cryptographic operations required to secure your communications but
sending them is up to you. You can copy & paste messages into your favorite chat
application that doesn't provide it's own e2e implementation; you can email 
messages to your contacts, you can even print the message off, stick it on the
back of a postcard and snail mail it to your Gran. 

## Why is this?

This was built due to increasing pressure on companies to ditch e2e encryption
for various, spurious governmental reasons. This library puts the decision to 
use or not to use e2e encryption in the hands of the user and not the service 
provider.

## Is this secure, Can I use it?

Maybe.

I'm relatively good at crypto stuff however I threw this together in about 2 hours
it's not been audited, it's not even been fully tested yet. It uses libsodium so
there's a relatively good chance there are no major issues in the code but I 
implore you not to trust it with anything remotely sensitive.

That said, please feel free to use it for testing purposes and for fun purposes

## What does the future look like?

I intend to develop this to a point that it is easy for a technically minded 
user to install and use correctly and securely. 

I hope that this project might spawn other projects, some of which might lead
to an app with a nice UI that provides the same functionality in a user-friendly
way to non-technical users. I may even lead this effort myself.

## License

MIT + the following

You must not use this software for protecting communications that if the 
communications in question were to be revealed to a third party, those 
communications would put one or more persons life at risk. 

I'm really not confident enough in my code for that.
