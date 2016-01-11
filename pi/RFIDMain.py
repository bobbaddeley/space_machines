import requests
import json
import datetime
import sys
import time 
import datetime 
import RPi.GPIO as io 
import select
from time import gmtime, strftime
import subprocess
import os
import MachineLogic
import DoorLogic
import MachinesAPI

web_api_url = "http://www.sector67.org/blog/api/"
#the machineID is the ID defined on the machines plugin for this particular machine
#unique for every tool and door in the space
machineID = 2
localRFID = ""

#reboot every 24 hours. Why? Because it makes sure the thing continues to run? Not sure really. Could just eliminate this, honestly.
rebootTime = time.time() + 86400

#web services api stuff
authService = MachinesAPI.MachinesAPI()
authService.SetMachineID(machineID)
authService.SetAPIUrl(web_api_url)

#machine specific stuff
#Uncomment these two lines for a typical 3D Printer self reporting machine
#machine = MachineLogic.MachineLogic()
#machine.Setup(authService)

#Uncomment these two lines for a door
machine = DoorLogic.DoorLogic()
machine.Setup(authService)

#loop forever
while True:
	try:
	    # read the standard input to see if the RFID has been swiped
	    while sys.stdin in select.select([sys.stdin],[],[],0)[0]:
		localRFID = sys.stdin.readline()
		if localRFID:
		    localRFID = ''.join(localRFID.splitlines())
		    #RFID has been swiped now check if authorized
		    #Swiping does the charge
		    #print(localRFID)
		    if authService.IsRFIDAuthorized(localRFID):
		       machine.DoAuthorizedWork(localRFID)
		    else:
		       machine.SetUnauthorizedError()

	    machine.DoUnAuthorizedContinuousWork()

	    time.sleep(.1)

	    if  time.time() > rebootTime:
		print("rebooting")
		os.system("reboot")
	except Exception as e:
		print("Unknown Error")
		print e

