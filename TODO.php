<?php
# TODO: Add 2FA key column to the Login Table
# TODO: Create generator for new keys
# TODO: Find a way for PHP to create a TOTP

# TODO: For loans, requests can go in, and should be saved in a LoanRequest table, and loan shark employees would have to accept them.

# FIXME: Fix the phone number regex for the signup page

# FIXME: Find out why this error "ArrayFATAL: terminating connection due to administrator command SSL connection has been closed unexpectedly server closed the connection unexpectedly This probably means the server terminated abnormally before or while processing the request." keeps popping up after another role has been assigned.

# TODO: Check if the verification email link has been used with an hour, if not, generate a new one and send it.
# TODO: Let JS see the "Response" header and display it as an alert
