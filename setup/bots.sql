GRANT SELECT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO cs425;

CREATE ROLE verifybot WITH PASSWORD 'a12dd3a7fd3203a452eb34d91a9be20569d5e337a3384347068895c07f3e0c5a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE AwaitingVerification TO verifybot;
GRANT SELECT, UPDATE ON TABLE Customers TO verifybot;  -- Can't run update without SELECT, why, I don't know why https://stackoverflow.com/questions/68023530/why-update-permission-does-not-work-without-select-permission-in-postgresql
GRANT SELECT ON TABLE Logins TO verifybot;
GRANT SELECT ON TABLE EmployeeLogins TO verifybot;
GRANT CONNECT ON DATABASE cs425 TO verifybot;


CREATE ROLE loanbot WITH PASSWORD '669e2e48e6abe564fda82128f42e15609c22778d1b47c22960bba05799bfdc7a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE LoanRequests TO loanbot;
GRANT SELECT, INSERT, DELETE ON TABLE LoanApprovals TO loanbot;
GRANT CONNECT ON DATABASE cs425 TO loanbot;


CREATE ROLE profilebot WITH PASSWORD '1900eab6c028483d7126599ee6f50de0d27907b5c65fa90524580b4b0f9852b0' LOGIN;
GRANT SELECT, UPDATE ON TABLE Customers TO profilebot;
GRANT SELECT, UPDATE ON TABLE Account TO profilebot;
GRANT SELECT ON TABLE AuthorizedUsers TO profilebot;
GRANT SELECT ON TABLE LoanApprovals TO profilebot;
GRANT CONNECT ON DATABASE cs425 TO profilebot;
-- GRANT SELECT, UPDATE ON TABLE Logins TO profilebot;  # TODO: If there is time, add the ability to let users change their email and password
