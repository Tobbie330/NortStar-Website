FROM python:3.12-slim

WORKDIR /srv/homehub

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app ./app
COPY web ./web

ENV HUB_CONFIG=/srv/homehub/config.yaml \
    HUB_SECRET_FILE=/data/homehub_secret

VOLUME /data

EXPOSE 8100
CMD ["uvicorn", "app.main:app", "--host", "0.0.0.0", "--port", "8100"]
