
def init():
    global terminate_program

    terminate_program = False

def shutdown_gracefully():
    global terminate_program

    return terminate_program

def shutdown():
    global terminate_program

    terminate_program = True
