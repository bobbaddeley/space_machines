#!/usr/bin/python
import sys
import time 
import datetime 
import RPi.GPIO as io 
import select
import MachinesAPI
import RFIDDataAccess
import subprocess
import glob
import os
import Adafruit_CharLCDPlate
from Adafruit_I2C import Adafruit_I2C

#This is for a typical machine, but in this case a 3D printer scale. The hardware is a pi with a LCD Button plate and a usb rfid reader.
#The user presses up/down to set the number of grams, then swipes to charge their account by that amount.
class MachineLogic:

    rfid =0
    grams = 0
    charge = 0
    balance = 0

    lcd = Adafruit_CharLCDPlate.Adafruit_CharLCDPlate()
    prev = -1
    btn = ((lcd.SELECT, 'Select'),
           (lcd.LEFT  , 'Left'  ),
           (lcd.UP    , 'Up'    ),
           (lcd.DOWN  , 'Down'  ),
           (lcd.RIGHT , 'Right' ))
    authService = None
    lastpush = datetime.datetime.now()
    
    #set up the hardware
    def Setup(self, authService):
	self.authService = authService
        io.setmode(io.BCM)
        self.lcd.begin(16, 2)	
        self.LCDRefresh = True
        self.currentstate = "IDLE"
	self.lcd.backlight(0)

    #unauthorized work involves the things before the RFID is swiped. That means using the buttons to adjust the weight.
    #if the buttons haven't been pressed for 30 seconds, we assume the user abandoned the job and we reset
    def DoUnAuthorizedContinuousWork(self):
        self.CheckButton()
        self.UpdateLCD()
        timelapse = (datetime.datetime.now()-self.lastpush)
	self.lcd.backlight2(1)
        if (timelapse.seconds > 30):
           self.grams = 0
	   self.lcd.backlight2(0)
           self.LCDRefresh = True
           self.currentstate = "IDLE"
           self.lastpush = datetime.datetime.now() + datetime.timedelta(days=10)

    #after the user swipes and the use of the machine is authorized, we charge the user and thank them
    def DoAuthorizedWork(self, rfid):
	self.rfid = rfid
        user = self.authService.GetUserByRFID(self.rfid)
        self.fullname = user["message"]["display_name"]
        results = self.authService.AddMachinePayment(int(self.rfid),self.grams)
        self.charge = results["message"]["charge"]
        self.balance = results["message"]["balance"]
        self.currentstate = "PAYMENT"
        self.LCDRefresh = True

    #basically a state machine for the UI.
    def UpdateLCD(self):
        if self.LCDRefresh == True:
            if self.grams > 0:
                self.lcd.clear()
                self.lcd.message("grams:" + "{0}".format(self.grams))
            if self.currentstate== "PAYMENT":
                self.lcd.clear()
                self.lcd.message("  Thank You   \n" + self.fullname)
                self.grams = 0
                self.lastpush = datetime.datetime.now() + datetime.timedelta(days=10)
	        time.sleep(2)
                self.lcd.clear()
                self.lcd.message("Charged:"+"{0}".format(self.charge)+"\nBalance:" + "{0}".format(round(self.balance,2)))
	        time.sleep(5)
                self.LCDRefresh = True
                self.currentstate = "IDLE"
                self.lcd.clear() 
                self.lcd.message(" Push Up/Down \nTo Set Weight ")
            elif self.currentstate== "ON":
                self.lcd.clear() 
                self.lcd.message("  Please Swipe  \n    RFID Tag   ")
            elif self.currentstate== "IDLE":
                self.lcd.clear() 
                self.lcd.message(" Push Up/Down \nTo Set Weight ")
	    elif self.currentstate=="UNAUTHORIZED":
		self.lcd.clear()
                self.lcd.message("  Unauthorized \n   RFID Tag  ")
                self.LCDRefresh = True
                self.currentstate = "ON"
                self.lastpush = datetime.datetime.now() + datetime.timedelta(days=10)
	        time.sleep(10)
                self.lcd.clear() 
                self.lcd.message("grams:" + "{0}".format(self.grams))
            self.LCDRefresh = False

    def SetUnauthorizedError(self):
        self.LCDRefresh = True
	self.currentstate = "UNAUTHORIZED"

    #when the buttons are pressed (only up/down do anything) it adjusts the weight to charge.
    def CheckButton(self):
        for self.b in self.btn:
	    if self.lcd.buttonPressed(self.b[0]):
                #if self.b is not self.prev:
		    print(self.b[1])
		    if self.b[1] == "Down":
                       if(self.grams != 0 ):
                          self.grams = self.grams - 1
                          print(self.grams)
                       self.lastpush = datetime.datetime.now()
                       self.LCDRefresh = True
		    if self.b[1] == "Up":                  
                       self.grams = self.grams + 1
                       print(self.grams)
                       self.lastpush = datetime.datetime.now()
                       self.LCDRefresh = True
		    #self.prev = self.b
                    self.LCDRefresh = True
                    self.currentstate = "INUSE"
             
