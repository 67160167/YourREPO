class Stack:
    def __init__(self):
        self.stack = []

    def push(self, item):
        self.stack.append(item)

    def pop(self):
        if len(self.stack) == 0:
            return f"Stack is Empty!"
        else:
            index = self.stack[-1]
            self.stack.pop()
            return index

    def peek(self):
        return f"{self.stack[-1]}"

    def is_empty(self):
            if len(self.stack) == 0:
                return True
            else:
                return False

    def size(self):
        size = len(self.stack)
        return f"{size}"


s = Stack()

s.push(1)
s.push(2)
s.push(3)
s.push(4)
s.push(5)

print("Size after push: ", s.size())
print("Top element:", s.peek())

print("Pop:", s.pop())
print("Pop:", s.pop())
print("Pop:", s.pop())
print("Pop:", s.pop())
print("Pop:", s.pop())

print("is empty:", s.is_empty())
print("Pop from empty:", s.pop())
