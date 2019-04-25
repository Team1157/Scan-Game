import json

import gspread
from oauth2client.service_account import ServiceAccountCredentials

## Sheets
scope = ['https://www.googleapis.com/auth/spreadsheets']
credentials = ServiceAccountCredentials.from_json_keyfile_name('google.json', scope)
gc = gspread.authorize(credentials)
wks = gc.open_by_key("1ldaYdnEgn_ie_lfsA9kLJfEzzWhKtGqZHMb70Jx9rIQ").sheet1

start = 3
end = 2508
cols = 5
cell_list = wks.range(f'A{start}:E{end}')

for r in range(start, end):
    for c in range(0,cols):
        cell_list[x * c + 0].value = lines - 3 + x
        cell_list[x * c + 1].value = user["name_first_preferred"]
        cell_list[x * c + 2].value = user["name_first"]

