import cv2
import pytesseract
import re
import requests
import json
from datetime import datetime, timedelta

from api_bridge import Bridge

bridge = Bridge()


class Location:
    """
    Handles location methods and tasks like reporting scans, handling errors and reading the config
    """

    def __init__(self):
        """
        Reads the config and initializes some defaults
        """
        with open("conf.json", "r") as f:
            self.data = json.load(f)
        self.password = self.data["password"]
        self.location_id = self.data["location_id"]
        self.base_url = self.data["base_url"]

        self.location_name = ""
        self.ready = False
        self.authenticated = False
        self.scanning = False
        self.token = ""
        self.next_scan_allowed = datetime.now()

    @staticmethod
    def handle_error(code: int, error_message: str):
        """
        Handles errors from the server by either raising an Exception in case of a fatal error.
        With non-fatal errors returns True for success and False in case of no success. Also communicates to bridge
        :param code: Error code from server
        :param error_message: Error message from server
        :return: True for success, False for failure, raises Exception otherwise
        """
        if code == 1:
            raise Exception(f"Error code 1, database error! Got error_message {error_message}, Exiting...")
        elif code == 2:
            raise Exception(f"Error code 2, missing input! Got error_message {error_message}, Exiting...")
        elif code == 3:
            raise Exception(f"Error code 3, invalid input! Got error_message {error_message}, Exiting...")
        elif code == 4:
            raise Exception(f"Error code 4, Wrong token or password. Got error_message {error_message}, Exiting...")
        elif code == 6:
            raise Exception(f"Error code 6, Does not exist. Got error_message {error_message}, Exiting...")
        elif code == 0:
            # Everything is fine
            return True
        elif code == 5:
            # Scanned too recently
            bridge.warning_scanned_recent()
            return False
        elif code == 7:
            # Failed scanning
            bridge.warning_malformed_name()
            return False

    def authenticate(self) -> None:
        """
        Authenticates the client, obtains token
        :return: Nothing
        """
        response = requests.post(f"{self.base_url}/ptcfg.php",
                                 data={"password": self.password, "loc": self.location_id})
        response_json = response.json()
        if self.handle_error(response_json["err"], response_json["errmsg"]):
            self.authenticated = True
            self.ready = True
            self.token = response_json["token"]
            self.location_name = response_json["name"]
            print(f"Logged in successfully, got token {self.token}")

    def report_scan(self, user_id: int, first: str, last: str) -> None:
        """
        Reports a scan to the server
        :param user_id: Student ID
        :param first: First name
        :param last: Last name
        :return: Nothing
        """
        data = {
            "id": str(user_id),
            "first": first,
            "last": last,
            "loc": str(self.location_id),
            "token": self.token,
        }
        response = requests.post(f"{self.base_url}/receiver.php", data=data)
        response_json = response.json()
        if self.handle_error(response_json["err"], response_json["errmsg"]):
            bridge.success_scan(user_id=response_json["user_id"], user_name=response_json["user_name"],
                                user_team=response_json["user_team"], created=response_json["user_created"])
        else:
            bridge.warning_scanned_recent()


def ocr_extract(text: str):
    """
    Removes unwanted characters from string, then tries to extract ID, First and Last
    :param text: the text to extract from
    :return: list of [id, first, last], unknown elements are None
    """
    allowed_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ:, "
    # String without disallowed characters
    simple_string = ''.join(i for i in text if i in allowed_chars)
    # Tries to find name using Regex
    name_search_result = re.search("[A-Z]+, [A-Z]+( |$)", simple_string)
    # Name is none if not found
    name = name_search_result[0] if name_search_result else None
    # Tries to find ID using Regex
    id_search_result = re.search("[4-5][0-9]{5}", text)
    # User ID is none if not found
    user_id = int(id_search_result[0]) if id_search_result else None
    # If we don't find a name return None for first and last and the id
    if not name:
        return user_id, None, None
    else:
        # If name split it into first and last, remove spaces
        first = name.split(",")[1].replace(" ", "")
        last = name.split(",")[0].replace(" ", "")
        return user_id, first, last


def main():
    location = Location()
    location.authenticate()
    cap = cv2.VideoCapture(1)

    while True:
        if datetime.now() > location.next_scan_allowed:
            ret, frame = cap.read()
            gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
            ocr_result = pytesseract.image_to_string(gray)
            if ocr_result:
                output = ocr_extract(ocr_result)
                if output[0] and output[1] and output[2]:
                    print(f"ID: {output[0]} First: {output[1]} Last: {output[2]}")
                    location.report_scan(output[0], output[1], output[2])
                    location.next_scan_allowed = datetime.now() + timedelta(seconds=5)
                else:
                    bridge.status_scanned_no_extract()
            else:
                bridge.status_scanning()


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("Shutting down...")
        exit(0)
