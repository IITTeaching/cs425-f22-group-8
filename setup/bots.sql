GRANT SELECT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO cs425;

CREATE ROLE bankbot WITH PASSWORD 'b299ecdcc0c02a319625205a804991255fdff2470dddbc7fa05c0c3934dbffae' LOGIN;
GRANT SELECT ON TABLE EmployeeLogins TO bankbot;
GRANT CONNECT ON DATABASE cs425 TO bankbot;


CREATE ROLE verifybot WITH PASSWORD 'a12dd3a7fd3203a452eb34d91a9be20569d5e337a3384347068895c07f3e0c5a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE AwaitingVerification TO verifybot;
GRANT SELECT, UPDATE ON TABLE Customers TO verifybot;  -- Can't run update without SELECT, why, I don't know https://stackoverflow.com/questions/68023530/why-update-permission-does-not-work-without-select-permission-in-postgresql
GRANT SELECT ON TABLE Logins TO verifybot;
GRANT SELECT ON TABLE EmployeeLogins TO verifybot;
GRANT CONNECT ON DATABASE cs425 TO verifybot;


CREATE ROLE loanbot WITH PASSWORD '669e2e48e6abe564fda82128f42e15609c22778d1b47c22960bba05799bfdc7a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE LoanRequests TO loanbot;
GRANT SELECT, INSERT, DELETE ON TABLE LoanApprovals TO loanbot;
GRANT SELECT ON Employee TO loanbot;
GRANT CONNECT ON DATABASE cs425 TO loanbot;


CREATE ROLE profilebot WITH PASSWORD '1900eab6c028483d7126599ee6f50de0d27907b5c65fa90524580b4b0f9852b0' LOGIN;
GRANT SELECT, UPDATE ON TABLE Customers TO profilebot;
GRANT SELECT, UPDATE, INSERT ON TABLE Account TO profilebot;
GRANT USAGE, SELECT ON SEQUENCE account_number_seq TO profilebot;
GRANT SELECT ON TABLE AuthorizedUsers TO profilebot;
GRANT SELECT ON TABLE LoanApprovals TO profilebot;
GRANT SELECT ON get_account_types TO profilebot;
GRANT CONNECT ON DATABASE cs425 TO profilebot;
-- GRANT SELECT, UPDATE ON TABLE Logins TO profilebot;  # TODO: If there is time, add the ability to let users change their email and password


CREATE ROLE addressbot WITH PASSWORD 'd80c9bf910f144738ef983724bc04bd6bd3f17c5c83ed57bedee1b1b9278e811' LOGIN;
GRANT SELECT, UPDATE, DELETE ON TABLE Addresses TO addressbot;
GRANT SELECT, UPDATE, DELETE ON TABLE Branch TO addressbot;
GRANT CONNECT ON DATABASE cs425 TO addressbot;


CREATE ROLE managerbot WITH PASSWORD '987bfb848c2403e638bf794771a1c3ffcda8cb1a312b07f38a376a6be35e9feb' LOGIN;
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE Employee TO managerbot;
GRANT SELECT, INSERT, UPDATE, DELETE ON TABLE EmployeeLogins TO managerbot;
GRANT USAGE, SELECT ON SEQUENCE employee_id_seq TO managerbot;
GRANT CONNECT ON DATABASE cs425 TO managerbot;


CREATE ROLE tellerbot WITH PASSWORD '11c8f9062973b50f228286368332495df3938e8902c87a2a4d738d7755c32039' LOGIN;
GRANT SELECT ON Employee TO tellerbot;
GRANT SELECT ON Account TO tellerbot;
GRANT CONNECT ON DATABASE cs425 TO tellerbot;


CREATE ROLE transactionsbot WITH PASSWORD '81dc075c3d55230215300137991a25f90be4c243a55580fe2af7538774147bd6' LOGIN;
GRANT SELECT, INSERT ON Transactions TO transactionsbot;
GRANT USAGE, SELECT ON SEQUENCE transactions_tid_seq TO transactionsbot;
GRANT SELECT, UPDATE ON Account TO transactionsbot;
GRANT SELECT ON AuthorizedUsers TO transactionsbot;
GRANT CONNECT ON DATABASE cs425 TO transactionsbot;