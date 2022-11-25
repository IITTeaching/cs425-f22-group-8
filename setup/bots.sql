CREATE ROLE verifybot WITH PASSWORD 'a12dd3a7fd3203a452eb34d91a9be20569d5e337a3384347068895c07f3e0c5a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE AwaitingVerification TO verifybot;
GRANT SELECT, UPDATE ON TABLE Customers TO verifybot;  -- Can't run update without SELECT, why, I don't know why https://stackoverflow.com/questions/68023530/why-update-permission-does-not-work-without-select-permission-in-postgresql
GRANT CONNECT ON DATABASE cs425 TO verifybot;


CREATE ROLE loanbot WITH PASSWORD '669e2e48e6abe564fda82128f42e15609c22778d1b47c22960bba05799bfdc7a' LOGIN;
GRANT SELECT, INSERT, DELETE ON TABLE LoanRequests TO loanbot;
GRANT SELECT, INSERT, DELETE ON TABLE Loans TO loanbot;
