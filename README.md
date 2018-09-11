# GoIP-SMS-Receiver
This application can be used to retrieve and store received SMS from a GoIP gateway into a mysql database. This application opens a UDP socket on all interfaces and will receive, parse and store any messages transmitted to it from a GoIP gateway into a database.

Install MySql and create a table "receive" with the following columns 
"srcnum" VARCHAR(14), "msg" VARCHAR(180), "goipname" VARCHAR(14).


