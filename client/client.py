import cv2
import pytesseract
import re
import requests
import json


class Location:
    def __init__(self):
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

    @staticmethod
    def handle_error(code: int, error_message: str):
        if code == 0:
            return True
        elif code == 1:
            raise Exception(f"Error code 1, database error! Got error_message {error_message}, Exiting...")
        elif code == 2:
            raise Exception(f"Error code 2, missing input! Got error_message {error_message}, Exiting...")
        elif code == 3:
            raise Exception(f"Error code 3, invalid input! Got error_message {error_message}, Exiting...")
        elif code == 4:
            raise Exception(f"Error code 4, Wrong token or password. Got error_message {error_message}, Exiting...")
        elif code == 5:
            return False
        elif code == 6:
            raise Exception(f"Error code 6, Does not exist. Got error_message {error_message}, Exiting...")

    def authenticate(self):
        response = requests.post(f"{self.base_url}/ptcfg.php",
                                 data={"password": self.password, "loc": self.location_id})
        response_json = response.json()
        if self.handle_error(response_json["err"], response_json["errmsg"]):
            self.authenticated = True
            self.ready = True
            self.token = response_json["token"]
            self.location_name = response_json["name"]
            print(f"Logged in successfully, got token {self.token}")

    def report_scan(self, user_id: int, user_name: str):
        data = {
            "id": str(user_id),
            "name": user_name,
            "loc": str(self.location_id),
            "token": self.token,
        }
        response = requests.post(f"{self.base_url}/receiver.php", data=data)
        response_json = response.json()
        if self.handle_error(response_json["err"], response_json["errmsg"]):
            print(f"Successfully scanned {user_id}")
        else:
            print(f"Scanned too recently")


def ocr_extract(text: str):
    allowed_chars = list("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ:, ")
    simple_string = ''.join(i for i in text if i in allowed_chars)
    name_search_result = re.search("[A-Z]+, [A-Z]+( |$)", simple_string)
    id_search_result = re.search("[4-5][0-9]{5}", text)
    name = name_search_result[0] if name_search_result else None
    user_id = int(id_search_result[0]) if id_search_result else None
    return user_id, name


def main():
    location = Location()
    location.authenticate()
    cap = cv2.VideoCapture(1)

    while True:
        ret, frame = cap.read()
        gray = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
        output = ocr_extract(pytesseract.image_to_string(gray))
        if output[0] and output[1]:
            print(f"ID: {output[0]} Name: {output[1]}")
            location.report_scan(output[0], output[1])


if __name__ == "__main__":
    try:
        main()
    except KeyboardInterrupt:
        print("Shutting down...")
        exit(0)
