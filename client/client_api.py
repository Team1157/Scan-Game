from flask import Flask, jsonify, request
from datetime import datetime
import json
from flask_cors import CORS


class Event:
    def __init__(self, event_level: int, event_type: str, extra_data=None):
        self.event_level = event_level
        self.event_type = event_type
        self.extra_data = extra_data
        self.time = datetime.now()
        self.is_read = False

    def mark_read(self):
        self.is_read = True

    def get_as_object(self):
        return {"event_level": self.event_level, "event_type": self.event_type, "time": self.time,
                "extra_data": self.extra_data}


class ApiServer:
    def __init__(self):
        with open("conf.json", "r") as f:
            self.data = json.load(f)
        self.location_id = self.data["location_id"]
        self.events = []

    def receive_event(self, event_level, event_type, extra_data):
        print(f"Got event {event_type} at level {event_level} with extra data {extra_data}")
        self.events.append(Event(event_level=int(event_level), event_type=event_type, extra_data=extra_data))

    def get_event_smart(self):
        for event in self.events:
            if event.event_level > 0 and not event.is_read:
                event.mark_read()
                return event
        return self.events[-1]


app = Flask(__name__)
CORS(app)

api_server = ApiServer()


@app.route("/info/")
def info():
    return jsonify({"location_id": api_server.location_id})


@app.route("/notify_event/", methods=["POST"])
def notify_event():
    data: dict = request.get_json()
    event_level = data["event_level"]
    event_type = data["event_type"]
    if "extra_info" in data.keys():
        extra_data = data["extra_info"]
    else:
        extra_data = None
    api_server.receive_event(event_level=event_level, event_type=event_type, extra_data=extra_data)
    return jsonify(success=True)


@app.route("/get_event_smart/")
def get_event_smart():
    event = api_server.get_event_smart()
    return jsonify(event.get_as_object())


if __name__ == "__main__":
    app.run(port=5000, )
