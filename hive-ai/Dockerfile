FROM python:3

WORKDIR /app

COPY requirements.txt /app

RUN pip install --no-cache-dir -r requirements.txt

COPY . /app

ENV FLASK_RUN_PORT=7000

EXPOSE 7000

CMD ["flask", "run"]