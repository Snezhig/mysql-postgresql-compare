FROM mysql:8.0

RUN apt-get update && apt-get install -y wget && \
    wget http://mysqltuner.pl/ -O mysqltuner.pl && \
    wget https://raw.githubusercontent.com/major/MySQLTuner-perl/master/basic_passwords.txt -O basic_passwords.txt && \
    wget https://raw.githubusercontent.com/major/MySQLTuner-perl/master/vulnerabilities.csv -O vulnerabilities.csv