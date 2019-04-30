import requests

"""
Messaging codes:
    status: The current status of the scanner like scanning and found no text or scanning and could not extract from text
        - status_scanning: Scanning, finding nothing, Level 0
        - status_scan_no_extract: Scanning, found text, but not readable, Level 0
    success: A scan succeeded
        - success_scan: Location successfully claimed, Level 1
    warning: A warning like scanned too recently
        - warning_scanned_recent: Success scanning and reported, but not accepted because too recent location scan, Level 2
        - warning_malformed_name: Success scanning and reported, but not accepted because name unreadable, Level 2
"""


class Bridge:
    def __init__(self):
        self.previous_event_type = ""

    @staticmethod
    def _send_request(event_type: str, event_level: int, extra_info=None):
        if extra_info:
            data = {"event_level": event_level, "event_type": event_type, "extra_info": extra_info}
        else:
            data = {"event_level": event_level, "event_type": event_type}
        requests.post("http://127.0.0.1:5000/notify_event/", data=data)

    @staticmethod
    def _get_level(event_type: str) -> int:
        if event_type == "status_scanning":
            return 0
        elif event_type == "status_scan_no_extract":
            return 0
        elif event_type == "success_scan":
            return 1
        elif event_type == "warning_scanned_recent":
            return 2
        elif event_type == "warning_malformed_name":
            return 2
        else:
            raise Exception("That event_type doesn't exist, something has gone horribly wrong, REEEEEEEEEE")

    def report(self, event_type: str, extra_info=None):
        event_level = self._get_level(event_type)
        if event_level != 0:
            self._send_request(event_type=event_type, event_level=event_level, extra_info=extra_info)
            self.previous_event_type = event_type
        else:
            if self.previous_event_type != event_type:
                self.previous_event_type = event_type
                self._send_request(event_type=event_type, event_level=event_level, extra_info=extra_info)

    def status_scanning(self):
        self.report(event_type="status_scanning")

    def status_scanned_no_extract(self):
        self.report(event_type="status_scan_no_extract")

    def success_scan(self, user_id: int):
        self.report(event_type="success_scan", extra_info=user_id)

    def warning_scanned_recent(self):
        self.report(event_type="warning_scanned_recent")

    def warning_malformed_name(self):
        self.report(event_type="warning_malformed_name")


if __name__ == "__main__":
    raise Exception("Don't run this file. This is designed to import Bridge from!!")
