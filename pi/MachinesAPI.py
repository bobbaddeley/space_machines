#!/usr/bin/python
import json
import requests

class MachinesAPI:
    #the unique machine id, set in RFIDMain
    MachineID = 0;

    #use SetAPIUrl() to set it to the correct url
    web_api_url = ""

    def SetMachineID(self, MachineID):
	self.MachineID = MachineID

    def SetAPIUrl(self, url):
	self.web_api_url = url

    #if you store a local list of acceptable users, then internet connectivity is no longer required. That way if the internet goes down you don't lose door access. It does cause you to have to log access locally and then update the server later, but that will be implemented eventually.
    def GetAuthorizedUsers( self):
        response = requests.get(self.web_api_url + 'machine/get_rfids_for_machine/?machine_id={0}'.format(self.MachineID))
	#print(response)
	return response.json()  #result

    #pay for use of the machine. You need to have the machine id, the RFID of the user, and the number of units (whether it's in seconds, or ounces, or whatever the units associated with the machine are
    def AddMachinePayment ( self, RFID, Amount):
        response = requests.post(self.web_api_url + 'machine/log_machine_usage/?machine_id={0}&unit={1}&rfid={2}'.format(self.MachineID, Amount, RFID))
 	#print(response.json())
	return response.json() #result

    #self explanatory, really. Useful only for displaying the username or account information, not for regular authorization checking.
    def GetUserByRFID(self,RFID):
	response = requests.get(self.web_api_url + 'user/get_user_for_rfid/?rfid={0}'.format(RFID))
	#print(response.json())
	return response.json()  #result

    #does this RFID user have access to this machine? Binary result, and we really don't care why it failed.
    def IsRFIDAuthorized(self,RFID):
	response = requests.get(self.web_api_url + 'machine/log_in_rfid_on_machine/?rfid={0}&machine_id={1}'.format(RFID,self.MachineID))
	parsed = response.json()
	if (parsed["message"]=="ok"):
		return True
	else:
		return False
