from flask import Flask, jsonify, request
from datetime import datetime
import json


class Event:
    def __init__(self, event_level: int, event_type: str):
        self.event_level = event_level
        self.event_type = event_type
        self.time = datetime.now()
        self.is_read = False

    def mark_read(self):
        self.is_read = True

    def get_as_object(self):
        return {"event_level": self.event_level, "event_type": self.event_type, "time": self.time}


class ApiServer:
    def __init__(self):
        with open("conf.json", "r") as f:
            self.data = json.load(f)
        self.location_id = self.data["location_id"]
        self.events = []

    def receive_event(self, event_level, event_type):
        self.events.append(Event(event_level=int(event_level), event_type=event_type))

    def get_oldest_unread_important_event(self):
        for event in self.events:
            if event.event_level > 0:
                event.mark_read()
                return event
        return None

    def get_most_recent_event(self) -> Event:
        return self.events[-1]


app = Flask(__name__)

api_server = ApiServer()


@app.route("/info/")
def info():
    return jsonify({"location_id": api_server.location_id})


@app.route("/notify_event/", methods=["POST"])
def notify_event():
    data = request.form
    api_server.receive_event(event_level=data["event_level"], event_type=data["event_type"])
    return jsonify(success=True)


@app.route("/get_event_smart/")
def get_event_smart():
    event = api_server.get_oldest_unread_important_event()
    if not event:
        event = api_server.get_most_recent_event()
    return jsonify(event.get_as_object())


if __name__ == "__main__":
    app.run()
