CREATE OR REPLACE FUNCTION withdrawal(account_number INT, amount DOUBLE PRECISION, descript TEXT)
RETURNS DOUBLE PRECISION AS $withdrawn$
DECLARE
    withdrawn DOUBLE PRECISION;
    not_valid BOOL;
BEGIN
   SELECT INTO not_valid ((amount > balance) AND (NOT can_go_negative)) AS not_valid FROM Account WHERE number = account_number;
    IF not_valid THEN
        RAISE NOTICE 'This account can not withdrawal more money than it has.';
        withdrawn = 0;
    ELSE
        UPDATE Account SET balance = (balance - amount) WHERE number = account_number;
        INSERT INTO Transactions(account_number, type, amount, description) VALUES(account_number, 'Withdrawal', (SELECT -amount), descript);
        withdrawn = amount;
    END IF;

    RETURN withdrawn;
END
$withdrawn$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION deposit(account_number INT, amount DOUBLE PRECISION, descript TEXT)
    RETURNS DOUBLE PRECISION AS $deposit$
DECLARE
BEGIN
    UPDATE Account SET balance = (balance + amount) WHERE number = account_number;
    INSERT INTO Transactions(account_number, type, amount, description) VALUES(account_number, 'Deposit', amount, descript);
    RETURN amount;
END
$deposit$ LANGUAGE plpgsql;


CREATE OR REPLACE FUNCTION transfer(initial_account_number INT, final_account_number INT, amount DOUBLE PRECISION, descript TEXT)
    RETURNS VOID AS $$
DECLARE
    withdrawn DOUBLE PRECISION;
BEGIN
    SELECT deposit(final_account_number, (SELECT withdrawn FROM withdrawal(initial_account_number, amount, descript)), descript);
END
$$ LANGUAGE plpgsql;


SELECT withdrawal(1, 750, 'Electric Bill (Past Transaction 3)') AS amount;
SELECT deposit(1, 1000, 'Child Support (Past Transaction 2)') AS amount;
-- SELECT * FROM account WHERE number = 1;