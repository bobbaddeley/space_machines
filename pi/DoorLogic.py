#!/usr/bin/python
import sys
import time 
import datetime 
import RPi.GPIO as io 
import select
import os

class DoorLogic:   
    #DoorButtonPin is a button on the inside to disable the lock temporarily. Generally not used, but if the handle doesn't work or you need a way to open the door, this will do it. 
    DoorButtonPin = 23

    #DoorRelayPin goes to the relay that unlocks the door briefly.
    DoorRelayPin = 24

    BuzzPeriod = 5

    def Setup(self, authService):
        io.setmode(io.BCM)
	io.setup(self.DoorButtonPin, io.IN)
	io.setup(self.DoorRelayPin, io.OUT)
	io.output(self.DoorRelayPin,False)

    def DoUnAuthorizedContinuousWork(self):
   	if io.input(self.DoorButtonPin) == 1:
	   io.output(self.DoorRelayPin,True)
           #print("button push")
	   #print("door open")
	   time.sleep(BuzzPeriod)
           io.output(self.DoorRelayPin,False)
           #print("door closed")
        
    def DoAuthorizedWork(self, rfid):
	io.output(self.DoorRelayPin,True)
	#print("door open")
	time.sleep(BuzzPeriod)
	io.output(self.DoorRelayPin, False)
	#print("door closed")            
